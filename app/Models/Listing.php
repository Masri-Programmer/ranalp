<?php

namespace App\Models;

use App\Enums\ListingType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Log;
use Spatie\Translatable\HasTranslations;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AuctionListing;
use App\Models\DonationListing;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Listing extends Model implements HasMedia
{
    use HasTranslations, HasSlug, HasFactory, SoftDeletes, InteractsWithMedia;
    public $translatable = ['title', 'description'];
    protected $fillable = [
        'user_id',
        'category_id',
        'type',
        'address_id',
        'title',
        'slug',
        'description',
        'currency',
        'status',
        'is_featured',
        'published_at',
        'expires_at',
        'visibility',
        'views_count',
        'likes_count',
        'average_rating',
        'reviews_count',
        'comments_count',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'average_rating' => 'float',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'reviews_count' => 'integer',
        'comments_count' => 'integer',
        'is_liked_by_current_user' => 'boolean',
        'is_expired' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['is_liked_by_current_user', 'is_expired'];


    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function listable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn($model) => $model->getTranslation('title', 'en') ?: 'listing')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }


    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $listing = $this->where(function ($query) use ($value) {
            $query->where('slug', $value)
                ->orWhere('uuid', $value);

            if (is_numeric($value)) {
                $query->orWhere('id', $value);
            }
        })->first();

        if (!$listing) {
            return null;
        }

        $isPrivate = in_array($listing->type, ['private', ListingType::PRIVATE_OCCASION->value]);
        $isOwner = Auth::check() && Auth::id() === $listing->user_id;

        // Redirect to UUID if private, EXCEPT if we are the owner or already using it
        if ($isPrivate && $value !== $listing->uuid && !$isOwner) {
            abort(404);
        }

        return $listing;
    }
    public function getRouteKey()
    {
        return in_array($this->type, ['private', ListingType::PRIVATE_OCCASION->value])
            ? $this->uuid
            : $this->slug;
    }

    /**
     * Get all of the listing's media.
     * A listing can have many images, videos, etc.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }
    /**
     * Get the user that owns the listing.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category for the listing.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the address for the listing.
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function ownerAddress(): HasOneThrough
    {
        return $this->hasOneThrough(
            Address::class,
            User::class,
            'id',
            'addressable_id',
            'user_id',
            'id'
        )->where('addressable_type', User::class);
    }
    public function primaryAddress(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }
    /**
     * Get the reviews for the listing.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function likers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
    public function isLikedByCurrentUser(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return $this->likers()->where('user_id', Auth::id())->exists();
    }

    public function getIsLikedByCurrentUserAttribute(): bool
    {
        return $this->isLikedByCurrentUser();
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this
            ->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

        $this
            ->addMediaCollection('videos')
            ->acceptsMimeTypes(['video/mp4', 'video/quicktime']);
    }
    public function getPriceAttribute()
    {
        if (!$this->listable)
            return null;

        // Handle Auction Logic
        if ($this->listable_type === AuctionListing::class) {
            return $this->listable->current_bid ?? $this->listable->start_price;
        }

        // Handle Donation Logic
        if ($this->listable_type === DonationListing::class) {
            return $this->listable->target;
        }

        // Handle Standard Items
        if (property_exists($this->listable, 'price')) {
            return $this->listable->price;
        }

        return null;
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(300)
            ->sharpen(10);
    }

    /**
     * Determine if the listing has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        // 1. Check explicit status
        if (in_array($this->status, ['expired', 'sold', 'completed', 'withdrawn'])) {
            return true;
        }

        // 2. Check expiration date
        if ($this->expires_at && $this->expires_at->isPast()) {
            return true;
        }

        return false;
    }


    public function bids()
    {
        return $this->hasMany(Bid::class)->orderBy('amount', 'desc');
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function highestBid()
    {
        return $this->hasOne(Bid::class)->ofMany('amount', 'max');
    }

    public function auctionDetails()
    {
        return $this->morphTo(__FUNCTION__, 'listable_type', 'listable_id');
    }

    public function faqs()
    {
        return $this->hasMany(ListingFaq::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(ListingSubscription::class);
    }
    public function updates()
    {
        return $this->hasMany(ListingUpdate::class)->latest();
    }

    public function getOwnerAddressAttribute()
    {
        return $this->user->address;
    }
    public function scopePublic(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('type', '!=', ListingType::PRIVATE_OCCASION->value)
                ->orWhereNull('type');
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // 0. Privacy (Hide private listings by default)
        $query->public();

        // 1. Search
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        });

        // 2. Category
        $query->when($filters['category'] ?? null, function ($query, $slug) {
            if ($slug !== 'all') {
                $query->whereHas('category', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                });
            }
        });

        // 3. Types
        $query->when($filters['types'] ?? null, function ($query, $types) {
            $types = is_array($types) ? $types : [$types];

            $mappedClasses = [];
            foreach ($types as $type) {
                match ($type) {
                    'bid', 'auction' => $mappedClasses[] = AuctionListing::class,
                    'donate', 'donation' => $mappedClasses[] = DonationListing::class,
                    default => null,
                };
            }

            if (!empty($mappedClasses)) {
                $query->whereIn('listable_type', $mappedClasses);
            }
        });

        $hasPriceFilter = ($filters['min_price'] ?? null) !== null || ($filters['max_price'] ?? null) !== null;

        $query->when($hasPriceFilter, function (Builder $query) use ($filters) {
            $min = $filters['min_price'] ?? 0;
            $max = $filters['max_price'] ?? 1000000;

            $query->where(function ($q) use ($min, $max) {
                // 1. Search in Auctions
                $q->whereHasMorph('listable', [AuctionListing::class], function ($sq) use ($min, $max) {
                    $sq->where(function ($sub) use ($min, $max) {
                        // Only check columns that actually exist on 'auction_listings'
                        $sub->whereBetween('start_price', [$min, $max])
                            ->orWhereBetween('current_bid', [$min, $max])
                            ->orWhereBetween('purchase_price', [$min, $max]);
                    });
                });

                // 2. OR Search in Donations
                $q->orWhereHasMorph('listable', [DonationListing::class], function ($sq) use ($min, $max) {
                    // Only check columns that exist on 'donation_listings'
                    $sq->whereBetween('target', [$min, $max]);
                });

                // 3. OR Search in Fixed Price Items (if you have them)
                // $q->orWhereHasMorph('listable', [PurchaseListing::class], function ($sq) use ($min, $max) {
                //    $sq->whereBetween('price', [$min, $max]);
                // });
            });
        });

        // 5. Location
        $query->when($filters['city'] ?? null, function ($query, $city) {
            $query->whereHas('address', function ($q) use ($city) {
                $q->where('city', 'like', '%' . $city . '%');
            });
        });

        // 6. Sort
        $query->when($filters['sort'] ?? null, function ($query, $sort) {
            match ($sort) {
                'oldest' => $query->oldest(),
                default => $query->latest(),
            };
        }, fn($q) => $q->latest());

        return $query;
    }
}
