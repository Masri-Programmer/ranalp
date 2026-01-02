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
        :media="listing.media"
        :listing-id="listing.id"
    />
    <!-- 
    <section>
        <h2 class="mb-4 text-lg font-semibold text-foreground">
            {{ $t('listing_details.key_features') }}
        </h2>
        <div class="grid grid-cols-2 gap-x-8 gap-y-3">
            <div
                v-for="feature in keyFeatures"
                :key="feature.text"
                class="flex items-center space-x-3"
            >
                <component
                    :is="feature.icon"
                    class="h-5 w-5 flex-shrink-0 text-green-600"
                />
                <span class="text-sm text-muted-foreground">{{
                    $t(feature.text)
                }}</span>
            </div>
        </div>
    </section> -->
</template>

<script setup lang="ts">
import { formatCurrency } from '@/composables/useCurrency';
import { Listing } from '@/types/listings';
import { BarChart, CheckCircle, ShieldCheck, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AuctionStatsGrid from './details/AuctionStatsGrid.vue';
import ListingFaqSection from './details/ListingFaqSection.vue';
import ListingTabs from './ListingTabs.vue';
import NotificationSubscription from './NotificationSubscription.vue';
defineProps<{
    listing: Listing;
}>();

const totalCapitalGoal = 750000;
const currentCapital = 450000;
const investorCount = 52;

/**
 * Calculates the progress bar percentage.
 */
const progressPercentage = computed(() => {
    return (currentCapital / totalCapitalGoal) * 100;
});

const formattedTotalCapital = computed(() => formatCurrency(totalCapitalGoal));
const formattedCurrentCapital = computed(() => formatCurrency(currentCapital));

// --- Static Content ---

const keyFeatures = [
    { text: 'listing_details.features.verified', icon: CheckCircle },
    { text: 'listing_details.features.secure', icon: ShieldCheck },
    { text: 'listing_details.features.support', icon: Users },
    { text: 'listing_details.features.growth', icon: BarChart },
];

const faqItems = [
    {
        value: 'item-1',
        question: 'listing_details.faq.q1',
        answer: 'listing_details.faq.a1',
    },
    {
        value: 'item-2',
        question: 'listing_details.faq.q2',
        answer: 'listing_details.faq.a2',
    },
    {
        value: 'item-3',
        question: 'listing_details.faq.q3',
        answer: 'listing_details.faq.a3',
    },
    {
        value: 'item-4',
        question: 'listing_details.faq.q4',
        answer: 'listing_details.faq.a4',
    },
];
</script>
