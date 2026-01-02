<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { PropType, ref } from 'vue';

import Layout from '@/components/layout/Layout.vue';
import ListingAuctionForm from '@/components/listings/create/ListingAuctionForm.vue';
import ListingCommonDetails from '@/components/listings/create/ListingCommonDetails.vue';
import ListingDonationForm from '@/components/listings/create/ListingDonationForm.vue';
import ListingMediaUpload from '@/components/listings/create/ListingMediaUpload.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import { useLanguageSwitcher } from '@/composables/useLanguageSwitcher';
import { destroy, update } from '@/routes/listings';
import { Category } from '@/types';
import { AuctionListable, DonationListable, Listing } from '@/types/listings';

const { locale, availableLanguages } = useLanguageSwitcher();
const props = defineProps({
    listing: {
        type: Object as PropType<Listing>,
        required: true,
    },
    categories: {
        type: Array as PropType<Array<Category>>,
        required: true,
    },
    addresses: {
        type: Array as PropType<Array<any>>,
        required: true,
    },
});

const mediaUploadRef = ref<InstanceType<typeof ListingMediaUpload> | null>(
    null,
);

// --- 1. DETERMINE MODE & TYPE ---

// The Business Type (e.g. 'charity_action', 'private_occasion')
// We assume this doesn't change during Edit
const businessType = props.listing.type;

// The Technical Mode (auction, purchase, donation)
const detectMode = (): 'auction' | 'purchase' | 'donation' => {
    if (props.listing.listable_type.includes('AuctionListing')) {
        const listable = props.listing.listable as AuctionListable;
        // If there is a start_price, it's an Auction.
        // If start_price is null/0 but has purchase_price, it's 'purchase'.
        // (Adjust this logic if your DB stores 0.00 for nulls)
        if (listable.start_price && Number(listable.start_price) > 0) {
            return 'auction';
        }
        return 'purchase';
    }
    // Default fallback
    return 'donation';
};

const listingMode = ref<'auction' | 'purchase' | 'donation'>(detectMode());

// --- Translations ---
const initialTranslations = availableLanguages.value.reduce<
    Record<string, string>
>((acc, lang) => {
    acc[lang.code] = props.listing.title[lang.code] ?? '';
    return acc;
}, {});

const initialDescriptionTranslations = availableLanguages.value.reduce<
    Record<string, string>
>((acc, lang) => {
    acc[lang.code] = props.listing.description[lang.code] ?? '';
    return acc;
}, {});

// --- Media ---
const existingMedia = {
    images: props.listing.media.filter(
        (m: any) => m.collection_name === 'images',
    ),
    documents: props.listing.media.filter(
        (m: any) => m.collection_name === 'documents',
    ),
    videos: props.listing.media.filter(
        (m: any) => m.collection_name === 'videos',
    ),
};

// --- Form Setup ---
const form = useForm({
    _method: 'patch',

    // Common
    title: initialTranslations,
    description: initialDescriptionTranslations,
    category_id: props.listing.category_id as number | null,
    expires_at: props.listing.expires_at
        ? new Date(props.listing.expires_at)
        : null,
    address_id: props.listing.address_id as number | null,

    // Critical: Send the Business Type and Mode so Controller knows what to validate
    type: businessType,
    mode: listingMode.value,

    images: [] as File[],
    documents: [] as File[],
    videos: [] as File[],
    media_to_delete: [] as number[],

    // --- PURCHASE FIELDS (AuctionListing used as Buy Now) ---
    // Note: 'purchase' mode relies on 'purchase_price' in DB, but UI might refer to it as 'price'
    purchase_price:
        listingMode.value === 'purchase'
            ? (props.listing.listable as AuctionListable).purchase_price
            : ((props.listing.listable as AuctionListable)?.purchase_price ??
              null),

    // In 'purchase' mode, we might not have 'quantity' in AuctionListing unless you added it.
    // If AuctionListing has no quantity column, remove this or map it correctly.
    // Assuming AuctionListing has item_condition:
    item_condition:
        (props.listing.listable as AuctionListable)?.item_condition ?? 'new',

    // --- AUCTION FIELDS ---
    start_price:
        listingMode.value === 'auction'
            ? (props.listing.listable as AuctionListable).start_price
            : null,

    reserve_price:
        listingMode.value === 'auction'
            ? (props.listing.listable as AuctionListable).reserve_price
            : null,

    // Auction can ALSO have a purchase price (Buy Now option)
    // We map it above, but ensure it's loaded if mode is auction too

    starts_at: (props.listing.listable as AuctionListable)?.starts_at
        ? new Date((props.listing.listable as AuctionListable).starts_at!)
        : null,

    ends_at: (props.listing.listable as AuctionListable)?.ends_at
        ? new Date((props.listing.listable as AuctionListable).ends_at!)
        : null,

    // --- DONATION FIELDS ---
    target:
        listingMode.value === 'donation'
            ? Number((props.listing.listable as DonationListable).target)
            : null,

    is_capped:
        listingMode.value === 'donation'
            ? (props.listing.listable as DonationListable).is_capped
            : false,
});

const submit = () => {
    form.post(update.url({ listing: props.listing.id }), {
        preserveScroll: true,
    });
};

function handleMediaDelete(mediaIds: number[]) {
    form.media_to_delete = mediaIds;
}

const deleteListing = () => {
    form.delete(destroy.url({ listing: props.listing.id }));
};
</script>

<template>
    <Layout :link="update.url({ listing: props.listing.id })">
        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle class="text-2xl font-bold">
                        {{
                            $t('listings.edit.title', {
                                title: listing.title[locale],
                            })
                        }}
                    </CardTitle>
                    <CardDescription>
                        {{ $t('listings.edit.description') }}
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-8">
                    <div class="mb-4">
                        <span
                            class="text-sm font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            {{
                                $t(`createListing.types.${businessType}.title`)
                            }}
                        </span>
                    </div>

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
                        @media-delete="handleMediaDelete"
                        :existing-media="existingMedia"
                        :image-error="form.errors.images"
                        :document-error="form.errors.documents"
                        :video-error="form.errors.videos"
                    />

                    <div class="space-y-6">
                        <h3 class="border-b pb-2 text-base font-semibold">
                            {{ $t('createListing.sections.details') }}
                        </h3>

                        <ListingAuctionForm
                            v-if="
                                listingMode === 'auction' ||
                                listingMode === 'purchase'
                            "
                            :form="form"
                            :mode="listingMode"
                        />

                        <ListingDonationForm
                            v-if="listingMode === 'donation'"
                            :form="form"
                        />
                    </div>
                </CardContent>

                <CardFooter class="flex justify-between">
                    <AlertDialog>
                        <AlertDialogTrigger as-child>
                            <Button variant="destructive" type="button">
                                {{ $t('actions.delete') }}
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>{{
                                    $t('actions.delete_confirmation.title')
                                }}</AlertDialogTitle>
                                <AlertDialogDescription>{{
                                    $t(
                                        'actions.delete_confirmation.description',
                                    )
                                }}</AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>{{
                                    $t('actions.cancel')
                                }}</AlertDialogCancel>
                                <AlertDialogAction @click="deleteListing">{{
                                    $t('actions.confirm')
                                }}</AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>

                    <Button type="submit" :disabled="form.processing">
                        <span v-if="form.processing">{{
                            $t('actions.saving')
                        }}</span>
                        <span v-else>{{ $t('actions.save') }}</span>
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </Layout>
</template>
