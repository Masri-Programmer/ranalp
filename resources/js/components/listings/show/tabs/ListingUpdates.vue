<template>
    <ListingTimeline
        :listing-id="listingId"
        :updates="updates"
        :is-owner="isOwner"
        :next-page-url="nextPageUrl"
    />
</template>

<script setup lang="ts">
import { ListingUpdate } from '@/types/listings';
import ListingTimeline from './ListingTimeline.vue';

defineProps<{
    listingId: number;
    updates: ListingUpdate[];
    isOwner: boolean;
    nextPageUrl?: string | null;
}>();
</script>
