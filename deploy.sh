#!/bin/bash
#  chmod +x deploy.sh 
set -e

unset GIT_DIR
unset GIT_WORK_TREE
unset GIT_INDEX_FILE

PM2_PROCESS_NAME="ranalp-ssr"

echo "🚀 Starting deployment..."

php artisan down || true

git pull
# echo "📦 Installing Composer (PHP) dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# echo "📦 Installing npm dependencies..."
npm ci

echo "🧹 Clearing old Laravel caches..."
php artisan optimize:clear

echo "🛠️ Building assets for production (SSR)..."
# NODE_OPTIONS=--max-old-space-size=4096 npm run build:ssr
NODE_OPTIONS=--max-old-space-size=4096 npm run build

echo "🏃 Running database migrations..."
# php artisan migrate --force

echo "🔥 Caching configuration for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔥 Seeding Translations..."
php artisan translate:sync --all 
# echo "🗺️ Generating sitemap..."
# php artisan sitemap:generate

# echo "⚙️ Reloading SSR service with new code..."
# $HOME/bin/pm2 reload "$PM2_PROCESS_NAME"

php artisan up

echo "✅ Deployment finished successfully!"
