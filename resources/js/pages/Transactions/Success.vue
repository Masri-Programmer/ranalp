<script setup lang="ts">
import Layout from '@/components/layout/Layout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { home } from '@/routes';
import { show } from '@/routes/listings';
import { Link } from '@inertiajs/vue3';
import { CheckCircle2 } from 'lucide-vue-next';

defineProps<{
    transactionId: string;
    listing: {
        id: number;
        title: string;
    }
}>();
</script>

<template>
    <Layout>
        <div class="container-custom flex min-h-[60vh] flex-col items-center justify-center py-10">
            <Card class="w-full max-w-md text-center">
                <CardHeader>
                    <div class="mb-4 flex justify-center">
                        <CheckCircle2 class="h-16 w-16 text-green-500" />
                    </div>
                    <CardTitle class="text-2xl">{{ $t('payment_success.title') }}</CardTitle>
                    <CardDescription>
                        {{ $t('payment_success.description') }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid gap-4 text-left">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-medium text-muted-foreground">{{ $t('payment_success.transaction_id') }}</span>
                        <span class="font-mono text-xs">{{ transactionId }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-medium text-muted-foreground">{{ $t('payment_success.item') }}</span>
                        <span>{{ listing.title[$page.props.locale] }}</span>
                    </div>
                </CardContent>
                <CardFooter class="flex flex-col gap-2">
                    <Link :href="show.url(listing.id)" class="w-full">
                        <Button class="w-full" variant="outline">{{ $t('payment_success.back_to_listing') }}</Button>
                    </Link>
                    <Link :href="home()" class="w-full">
                        <Button class="w-full" variant="ghost">{{ $t('payment_success.go_home') }}</Button>
                    </Link>
                </CardFooter>
            </Card>
        </div>
    </Layout>
</template>