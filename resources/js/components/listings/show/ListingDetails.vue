<template>
    <AuctionStatsGrid :listing="listing" />
    <ListingFaqSection
        :listing-id="listing.id"
        :faqs="listing.faqs || []"
        :is-owner="$page.props.auth.user?.id === listing.user_id"
    />
    <NotificationSubscription :listing-id="listing.id" />
    <ListingTabs
        :reviews="listing.reviews"
        :next-page-url="listing.next_page_url"
        :updates="listing.updates"
        :next-update-page-url="listing.next_update_page_url"
        :media="listing.media"
        :listing-id="listing.id"
        :is-owner="$page.props.auth.user?.id === listing.user_id"
    />
</template>

<script setup lang="ts">
import { Listing } from '@/types/listings';
import AuctionStatsGrid from './details/AuctionStatsGrid.vue';
import ListingFaqSection from './details/ListingFaqSection.vue';
import ListingTabs from './ListingTabs.vue';
import NotificationSubscription from './NotificationSubscription.vue';
defineProps<{
    listing: Listing;
}>();
</script>
