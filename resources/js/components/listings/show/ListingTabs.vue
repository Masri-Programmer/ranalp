<template>
    <div class="w-full">
        <Tabs default-value="reviews" class="w-full rounded-lg border bg-card">
            <TabsList
                class="grid h-auto w-full grid-cols-3 rounded-none border-b p-0"
            >
                <TabsTrigger value="reviews" class="rounded-none py-3">
                    {{ $t('reviews.tabs.reviews') }} ({{ reviews.length }})
                </TabsTrigger>

                <TabsTrigger value="documents" class="rounded-none py-3">
                    {{ $t('reviews.tabs.documents') }} ({{
                        media?.documents?.length || 0
                    }})
                </TabsTrigger>

                <TabsTrigger value="updates" class="rounded-none py-3">
                    {{ $t('reviews.tabs.updates') }} ({{ updates.length }})
                </TabsTrigger>
            </TabsList>

            <TabsContent value="reviews" class="p-6">
                <ListingReviews
                    :reviews="reviews"
                    :listing-id="listingId"
                    :next-page-url="nextPageUrl"
                />
            </TabsContent>

            <TabsContent value="documents" class="p-6">
                <ListingDocuments :documents="media?.documents" />
            </TabsContent>

            <TabsContent value="updates" class="p-6">
                <ListingUpdates :updates="updates" :listing-id="listingId" />
            </TabsContent>
        </Tabs>
    </div>
</template>

<script setup lang="ts">
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ListingMediaCollection, Review, Update } from '@/types/listings';
import ListingDocuments from './tabs/ListingDocuments.vue';
import ListingReviews from './tabs/ListingReviews.vue';
import ListingUpdates from './tabs/ListingUpdates.vue';

const props = withDefaults(
    defineProps<{
        listingId: number;
        media?: ListingMediaCollection;
        reviews?: Review[];
        updates?: Update[];
        nextPageUrl?: string | null;
    }>(),
    {
        reviews: () => [],
        updates: () => [],
        nextPageUrl: null,
    },
);
</script>
