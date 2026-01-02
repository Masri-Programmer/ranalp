<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { useStorage } from '@vueuse/core';
import { useToast } from 'vue-toastification';

// Layout & UI
import Layout from '@/components/layout/Layout.vue';
import ListingAuctionForm from '@/components/listings/create/ListingAuctionForm.vue';
import ListingCommonDetails from '@/components/listings/create/ListingCommonDetails.vue';
import ListingDonationForm from '@/components/listings/create/ListingDonationForm.vue';
import ListingMediaUpload from '@/components/listings/create/ListingMediaUpload.vue';
import ListingTypeSelector from '@/components/listings/create/ListingTypeSelector.vue';

import ListingAside from '@/components/listings/show/ListingAside.vue';
import ListingDetails from '@/components/listings/show/ListingDetails.vue';
import ListingSlide from '@/components/listings/show/ListingSlide.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

import { useLanguageSwitcher } from '@/composables/useLanguageSwitcher';
import { create, store } from '@/routes/listings';
import { trans } from 'laravel-vue-i18n';
import { computed, PropType, ref, watch } from 'vue';

const { locale, availableLanguages } = useLanguageSwitcher();
const props = defineProps({
    categories: {
        type: Array as PropType<
            Array<{
                id: number;
                name: { [key: string]: string };
            }>
        >,
        required: true,
    },
    addresses: {
        type: Array as PropType<Array<any>>,
        required: true,
    },
});
const toast = useToast();

const mediaUploadRef = ref<InstanceType<typeof ListingMediaUpload> | null>(
    null,
);

const page = usePage();
const listingTypes = computed(() => page.props.listingTypes as string[]);

// Use the first type as default if available, otherwise 'private_occasion'
const defaultType = computed(() => listingTypes.value[0] || 'private_occasion');

const listingType = useStorage<string>(
    'create-listing-type',
    defaultType.value,
);

const getModeFromType = (type: string): 'donation' | 'auction' => {
    if (type === 'charity_action') return 'auction'; // Default to auction, can toggle to purchase
    return 'donation';
};

const listingMode = ref<'auction' | 'purchase' | 'donation'>(
    getModeFromType(listingType.value),
);
const initialTranslations = availableLanguages.value.reduce(
    (acc, lang) => {
        acc[lang.code] = '';
        return acc;
    },
    {} as { [key: string]: string },
);

const form = useForm({
    // Common
    title: { ...initialTranslations },
    description: { ...initialTranslations },
    category_id: null as number | null,
    expires_at: null as Date | null,
    images: [] as File[],
    documents: [] as File[],
    videos: [] as File[],
    address_id: null as number | null,
    type: listingType.value,
    mode: listingMode.value,

    // Buy Now
    price: null as number | null,
    quantity: 1,
    item_condition: 'new',

    // Auction
    start_price: null as number | null,
    reserve_price: null as number | null,
    purchase_price: null as number | null,
    starts_at: null as Date | null,
    ends_at: null as Date | null,

    // Donation
    target: null as number | null,
    is_capped: false,

    // Terms
    terms: false,
    processing: false,
});

watch(listingType, (newType) => {
    form.type = newType;

    // Automatically set the mode based on the selected type
    if (newType === 'charity_action') {
        // If switching TO charity action, default to auction (user can click "Buy Now" toggle)
        listingMode.value = 'auction';
    } else {
        // All other types (Private, Campaign, Founders) are purely Donation based
        listingMode.value = 'donation';
    }

    form.mode = listingMode.value;
    form.clearErrors();
});

watch(listingMode, (newMode) => {
    form.mode = newMode;
});

const submit = () => {
    if (!form.terms) {
        form.setError('terms', trans('createListing.terms.description'));
        return;
    }

    form.post(store.url(), {
        onSuccess: () => {
            form.reset();
            mediaUploadRef.value?.reset();
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};
const showPreview = ref(false);

// Helper to create object URLs for file previews
const getFilePreview = (file: File) => {
    return URL.createObjectURL(file);
};
const previewListing = computed(() => {
    // 1. Safe Defaults for Category/Address/User
    const selectedCategory = props.categories?.find(
        (c) => c.id === form.category_id,
    ) || { id: 0, name: { en: 'Category' }, icon: 'tag' };

    const selectedAddress = props.addresses?.find(
        (a) => a.id === form.address_id,
    ) || { id: 0, city: 'City', country: 'Country', label: 'Address' };

    const currentUser = page.props.auth?.user || {
        id: 0,
        name: 'You',
        profile_photo_url: null,
    };

    // 2. Determine Type string for Morph
    let morphType = 'App\\Models\\PurchaseListing'; // Default
    let listableData = {};

    if (form.mode === 'auction') {
        morphType = 'App\\Models\\AuctionListing'; //
        listableData = {
            id: 999, // Fake ID for listable
            start_price: form.start_price || 0,
            current_bid: form.start_price || 0,
            reserve_price: form.reserve_price || 0,
            purchase_price: form.purchase_price || 0,
            ends_at: form.ends_at
                ? form.ends_at
                : new Date(Date.now() + 86400000).toISOString(), // +1 day default
            starts_at: form.starts_at
                ? form.starts_at
                : new Date().toISOString(),
            item_condition: form.item_condition || 'new',
            bids_count: 0,
        };
    } else if (form.mode === 'donation') {
        morphType = 'App\\Models\\DonationListing'; //
        listableData = {
            id: 999,
            target: form.target || 1000,
            raised: 0,
            donors_count: 0,
            is_capped: form.is_capped || false,
            progress_percent: 0,
        };
    } else {
        // Purchase/Standard
        morphType = 'App\\Models\\PurchaseListing';
        listableData = {
            id: 999,
            price: form.price || 0,
            quantity: form.quantity || 1,
        };
    }

    // 3. Media Mapping
    const mediaImages = (form.images || []).map((file, index) => ({
        id: index,
        url: URL.createObjectURL(file),
        thumbnail: URL.createObjectURL(file),
        mime_type: file.type,
    }));

    // 4. Return Final Object
    return {
        id: 999999, // Non-null ID is crucial
        uuid: 'preview-uuid',
        user_id: currentUser.id,
        category_id: form.category_id || 0,
        address_id: form.address_id || null,

        // Main fields
        title: form.title,
        slug: 'preview-slug',
        description: form.description,
        type: form.type, // private_occasion, charity_action, etc.
        status: 'draft',
        currency: 'EUR',
        visibility: 'public',

        // Polymorphic Type (CRITICAL for ListingAside)
        listable_type: morphType,
        listable_id: 999,
        listable: listableData,

        // Relations
        user: currentUser,
        category: selectedCategory,
        address: selectedAddress,

        // Metrics & booleans
        views_count: 0,
        likes_count: 0,
        is_liked_by_current_user: false,
        is_expired: false,
        reviews_count: 0,
        comments_count: 0,
        average_rating: 0,
        is_featured: false,

        // Price helper
        price: form.price || form.start_price || form.target || 0,

        // Dates
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        published_at: new Date().toISOString(),
        expires_at: form.expires_at || null,
        deleted_at: null,

        meta: null,

        // Media
        media: {
            images: mediaImages,
            videos: [],
            documents: [],
        },
    } as any;
});
</script>

<template>
    <Layout :link="create.url()">
        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle class="text-2xl font-bold">
                        {{ $t('createListing.title') }}
                    </CardTitle>
                    <CardDescription>
                        {{ $t('createListing.description') }}
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-8">
                    <ListingTypeSelector v-model="listingType" />

                    <ListingCommonDetails
                        v-model:title="form.title"
                        v-model:description="form.description"
                        v-model:category_id="form.category_id"
                        v-model:address_id="form.address_id"
                        v-model:expires_at="form.expires_at"
                        :categories="props.categories"
                        :addresses="props.addresses"
                        :locale="locale"
                        :fallback-locale="'de'"
                        :errors="form.errors"
                    />

                    <ListingMediaUpload
                        ref="mediaUploadRef"
                        v-model:images="form.images"
                        v-model:documents="form.documents"
                        v-model:videos="form.videos"
                        :image-error="form.errors.images"
                        :document-error="form.errors.documents"
                        :video-error="form.errors.videos"
                    />

                    <div class="space-y-6">
                        <h3 class="border-b pb-2 text-base font-semibold">
                            {{ $t('createListing.sections.details') }}
                        </h3>

                        <!-- Sub-mode selector for Charity Action -->
                        <div
                            v-if="listingType === 'charity_action'"
                            class="mb-6 border-b pb-4"
                        >
                            <Label class="mb-2 block text-base font-semibold">
                                {{ $t('createListing.sections.mode_select') }}
                            </Label>
                            <div class="flex gap-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    :class="{
                                        'border-primary bg-primary/10':
                                            listingMode === 'auction',
                                    }"
                                    @click="listingMode = 'auction'"
                                >
                                    {{ $t('createListing.modes.auction') }}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    :class="{
                                        'border-primary bg-primary/10':
                                            listingMode === 'purchase',
                                    }"
                                    @click="listingMode = 'purchase'"
                                >
                                    {{ $t('createListing.modes.purchase') }}
                                </Button>
                            </div>
                        </div>

                        <ListingAuctionForm
                            v-if="
                                listingType === 'charity_action' &&
                                listingMode === 'auction'
                            "
                            :form="form"
                        />
                        <ListingDonationForm
                            v-if="listingTypes.includes(listingType)"
                            :form="form"
                        />
                    </div>
                </CardContent>

                <CardFooter>
                    <div class="flex w-full flex-col gap-4">
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="terms"
                                v-model="form.terms"
                                @update:model-value=""
                            />
                            <Label
                                for="terms"
                                class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                            >
                                {{ $t('createListing.terms.agree') }}
                                <a
                                    href="/terms-of-service"
                                    target="_blank"
                                    class="text-primary underline"
                                >
                                    {{ $t('createListing.terms.link') }}
                                </a>
                            </Label>
                        </div>

                        <Button type="submit" :disabled="form.processing">
                            <span
                                v-if="form.processing"
                                class="flex items-center gap-2"
                            >
                                {{ $t('createListing.buttons.submitting') }}
                            </span>
                            <span v-else>
                                {{ $t('createListing.buttons.submit') }}
                            </span>
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="w-full"
                            @click="showPreview = true"
                        >
                            {{ $t('createListing.buttons.preview') }}
                        </Button>
                        <p
                            v-if="form.errors.terms"
                            class="text-sm font-medium text-destructive"
                        >
                            {{ form.errors.terms }}
                        </p>
                    </div>
                </CardFooter>

                <div
                    v-if="showPreview"
                    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/80 p-4 backdrop-blur-sm"
                >
                    <div
                        class="relative my-8 w-full max-w-7xl rounded-lg bg-background shadow-xl"
                    >
                        <button
                            @click="showPreview = false"
                            class="absolute top-4 right-4 z-50 rounded-full bg-white p-2 text-black shadow hover:bg-gray-100"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-6 w-6"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>

                        <div class="max-h-[90vh] overflow-y-auto p-6">
                            <div
                                class="mb-6 border-l-4 border-yellow-500 bg-yellow-100 p-4 text-yellow-700"
                                role="alert"
                            >
                                <p class="font-bold">
                                    {{ $t('createListing.preview.mode') }}
                                </p>
                                <p>
                                    {{ $t('createListing.preview.notice') }}
                                </p>
                            </div>

                            <div class="flex-1 overflow-y-auto p-6">
                                <div
                                    v-if="previewListing && previewListing.id"
                                    class="relative grid grid-cols-1 gap-8 lg:grid-cols-3 lg:items-start"
                                >
                                    <div class="relative col-span-2 space-y-8">
                                        <!-- <ListingHeader
                                            :listing="previewListing"
                                        /> -->
                                        <ListingSlide
                                            :listing="previewListing"
                                        />
                                    </div>

                                    <ListingAside :listing="previewListing" />
                                </div>

                                <div
                                    v-if="previewListing && previewListing.id"
                                    class="mt-8"
                                >
                                    <ListingDetails :listing="previewListing" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>
        </form>
    </Layout>
</template>
