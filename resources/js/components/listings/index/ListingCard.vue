<script setup lang="ts">
import LikeAction from '@/components/actions/LikeAction.vue';
import ShareAction from '@/components/actions/ShareAction.vue';
import ListingAuctionContent from '@/components/listings/index/ListingAuctionContent.vue';
import ListingDonationContent from '@/components/listings/index/ListingDonationContent.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTranslation } from '@/composables/useTranslation';
import { show } from '@/routes/listings';
import { Listing } from '@/types/listings';
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { ExternalLink, MapPin, Star } from 'lucide-vue-next';
import { computed, PropType } from 'vue';

const { translate } = useTranslation();

const props = defineProps({
    listing: {
        type: Object as PropType<Listing>,
        required: true,
    },
});

function getListingType(type: Listing['listable_type'] | string): {
    text: string;
    variant: 'secondary' | 'outline' | 'destructive' | 'default';
} {
    switch (type) {
        case 'App\\Models\\AuctionListing':
            return {
                text: trans('filters.types.bid'),
                variant: 'secondary',
            };

        case 'App\\Models\\DonationListing':
            return {
                text: trans('filters.types.donate'),
                variant: 'destructive',
            };
        default:
            return { text: 'Unknown', variant: 'outline' };
    }
}

const listable = computed(() => {
    if (
        typeof props.listing.listable === 'object' &&
        props.listing.listable !== null
    ) {
        return props.listing.listable;
    }
    return null;
});
const shareUrl = computed(() => {
    return window.location.origin + show.url(props.listing);
});

const fallbackImage = 'https://placehold.co/600x400?text=No+Media+Available';

const handleImageError = (e: Event) => {
    const target = e.target as HTMLImageElement;
    target.src = fallbackImage;
};
</script>

<template>
    <Card>
        <Link :href="show.url(listing)" class="flex flex-col overflow-hidden">
            <CardHeader class="relative p-0">
                <img
                    :src="listing.image_url || fallbackImage"
                    :alt="translate(listing.title)"
                    class="h-48 w-full object-cover"
                    @error="handleImageError"
                />
                <div class="absolute top-4 left-4 flex gap-2">
                    <Badge
                        :variant="getListingType(listing.listable_type).variant"
                        class="text-xs"
                    >
                        {{ getListingType(listing.listable_type).text }}
                    </Badge>
                </div>
                <div class="absolute top-4 right-4 flex gap-2">
                    <ShareAction
                        :share-url="shareUrl"
                        :listing-title="translate(listing.title)"
                    />
                    <LikeAction :listing="listing" />
                </div>
            </CardHeader>

            <CardContent class="flex-grow pt-6">
                <span class="text-sm font-medium text-primary">{{
                    translate(listing.category.name)
                }}</span>
                <CardTitle class="mt-1 mb-2 text-xl font-bold">
                    {{ translate(listing.title) }}
                </CardTitle>
                <CardDescription
                    class="line-clamp-2 text-muted-foreground"
                    v-html="
                        translate(listing.description)
                            .substring(0, 100)
                            .concat('...')
                    "
                >
                </CardDescription>
                <div
                    class="mt-4 flex flex-col items-start gap-2 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-1">
                        <MapPin class="h-4 w-4" />
                        <span>{{ $t('locations.generic') }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <Star class="h-4 w-4 text-yellow-500" />
                        <span
                            >{{ listing.average_rating }} ({{
                                listing.reviews_count
                            }}
                            {{ $t('reviews.label') }})</span
                        >
                    </div>
                </div>

                <ListingAuctionContent
                    v-if="
                        listing.listable_type ===
                            'App\\Models\\AuctionListing' && listable
                    "
                    :listable="listable"
                />
                <ListingDonationContent
                    v-if="
                        listing.listable_type ===
                            'App\\Models\\DonationListing' && listable
                    "
                    :listable="listable"
                />
            </CardContent>

            <CardFooter class="flex flex-col gap-3">
                <div
                    class="flex w-full justify-between gap-2 border-t border-border pt-3"
                >
                    <Button
                        variant="ghost"
                        size="sm"
                        class="w-full justify-center text-xs"
                        as-child
                    >
                        <Link :href="show.url(listing)" @click.stop>
                            {{ $t('links.details') }}
                            <ExternalLink class="ml-1 h-3 w-3" />
                        </Link>
                    </Button>
                </div>
            </CardFooter>
        </Link>
    </Card>
</template>
