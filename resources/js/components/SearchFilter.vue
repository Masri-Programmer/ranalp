<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { Slider } from '@/components/ui/slider';
import { index } from '@/routes/listings';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { useStorage, useToggle } from '@vueuse/core';
import { Search, SlidersHorizontal, X } from 'lucide-vue-next';
import { computed, watch } from 'vue';

const emit = defineEmits<{
    (e: 'filterChange', filters: Record<string, any>): void;
}>();

const page = usePage();
const currentLocale = computed(() => (page.props.locale as string) || 'en');
const [showAdvanced, toggleAdvanced] = useToggle(false);

const formattedCategories = computed(() => {
    return page.props.categories.map((category: any) => ({
        id: category.id,
        slug: category.slug,
        name:
            category.name[currentLocale.value] ||
            category.name['en'] ||
            'Unknown Category',
    }));
});
const filters = computed(() => {
    return (page.props.filters || {}) as ServerFilters;
});

const DEFAULT_MIN_PRICE = 0;
const DEFAULT_MAX_PRICE = 1000000;

// Define the structure of the form
interface FormState {
    search: string;
    category: string;
    listingTypes: string;
    priceRange: number[];
    location: string;
    sortBy: string;
}

const defaultState = {
    search: '',
    category: 'all',
    listingTypes: 'all',
    priceRange: [DEFAULT_MIN_PRICE, DEFAULT_MAX_PRICE],
    location: '',
    sortBy: 'latest',
};

const storedFilters = useStorage('ranalp-filters', defaultState);
interface ServerFilters {
    search?: string;
    category?: string;
    types?: string | string[];
    min_price?: string | number;
    max_price?: string | number;
    city?: string;
    sort?: string;
}

const getInitialFormValues = () => {
    const serverFilters = filters.value;

    if (Object.keys(serverFilters).length > 0) {
        let safeType = 'all';
        if (serverFilters.types) {
            safeType = Array.isArray(serverFilters.types)
                ? serverFilters.types[0]
                : serverFilters.types;
        }

        const newState = {
            search: serverFilters.search || '',
            category: serverFilters.category || 'all',
            listingTypes: safeType,
            priceRange: [
                Number(serverFilters.min_price) || DEFAULT_MIN_PRICE,
                Number(serverFilters.max_price) || DEFAULT_MAX_PRICE,
            ],
            location: serverFilters.city || '',
            sortBy: serverFilters.sort || 'latest',
        };

        storedFilters.value = newState;
        return newState;
    }

    const cleanState = { ...defaultState };
    storedFilters.value = cleanState;

    return cleanState;
};

const form = useForm(getInitialFormValues());

watch(
    () => form.data(),
    (newData) => {
        storedFilters.value = newData;
    },
    { deep: true },
);

watch(
    () => filters.value,
    (newFilters) => {
        const defaults = { ...defaultState };

        let incomingType = defaults.listingTypes;
        if (newFilters?.types) {
            incomingType = Array.isArray(newFilters.types)
                ? newFilters.types[0]
                : newFilters.types;
        } else {
            incomingType = 'all';
        }

        form.search = newFilters?.search || defaults.search;
        form.category = newFilters?.category || defaults.category;
        form.listingTypes = incomingType;
        form.priceRange = [
            Number(newFilters?.min_price) || defaults.priceRange[0],
            Number(newFilters?.max_price) || defaults.priceRange[1],
        ];
        form.location = newFilters?.city || defaults.location;
        form.sortBy = newFilters?.sort || defaults.sortBy;
    },
    { deep: true },
);

// --- DYNAMIC LISTING TYPES ---
const listingTypes = computed(() => {
    const backendTypes = (page.props.listingTypes as string[]) || [];
    return [
        { id: 'all', labelKey: 'filters.allCategories' },
        ...backendTypes
            .filter((t) => t !== 'private_occasion') // Usually hidden from search filters
            .map((t) => ({
                id: t,
                labelKey: `createListing.types.${t}.title`,
            })),
    ];
});

const sortOptions = [
    { id: 'latest', labelKey: 'filters.sortOptions.recent' },
    { id: 'oldest', labelKey: 'filters.sortOptions.oldest' },
    { id: 'price-low', labelKey: 'filters.sortOptions.priceLow' },
    { id: 'price-high', labelKey: 'filters.sortOptions.priceHigh' },
    { id: 'popular', labelKey: 'filters.sortOptions.popular' },
    // { id: 'roi', labelKey: 'filters.sortOptions.roi' }, // Removed specific ROI sort if not applicable
];

function getListingTypeLabel(typeId: string) {
    const type = listingTypes.value.find((t) => t.id === typeId);
    return type ? type.labelKey : typeId;
}

function removeType() {
    form.listingTypes = 'all';
}

function clearFilters() {
    form.defaults(defaultState);
    form.reset();
    storedFilters.value = defaultState;
    applyFilters();
}

function applyFilters() {
    const isDefaultMinPrice = form.priceRange[0] === DEFAULT_MIN_PRICE;
    const isDefaultMaxPrice = form.priceRange[1] === DEFAULT_MAX_PRICE;
    const isDefaultPriceRange = isDefaultMinPrice && isDefaultMaxPrice;
    const isPriceSort =
        form.sortBy === 'price-low' || form.sortBy === 'price-high';

    const typeParam = form.listingTypes === 'all' ? null : form.listingTypes;

    const queryParams = {
        search: form.search,
        category: form.category === 'all' ? null : form.category,
        types: typeParam,
        min_price:
            isDefaultPriceRange && !isPriceSort ? null : form.priceRange[0],
        max_price:
            isDefaultPriceRange && !isPriceSort ? null : form.priceRange[1],
        city: form.location,
        sort: form.sortBy === 'latest' ? null : form.sortBy,
    };

    const cleanParams = Object.fromEntries(
        Object.entries(queryParams).filter(([_, v]) => v !== null && v !== ''),
    );

    router.get(index(), cleanParams, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
            toggleAdvanced(false);
        },
    });

    emit('filterChange', cleanParams);
}
</script>

<template>
    <div class="mb-4 rounded-lg border bg-card p-4 shadow-sm">
        <form
            @submit.prevent="applyFilters"
            class="relative flex items-center gap-2"
        >
            <div class="relative flex-1">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="form.search"
                    :placeholder="$t('filters.searchPlaceholder')"
                    class="pl-10"
                />
            </div>

            <Button
                type="submit"
                variant="outline"
                size="icon"
                :aria-label="$t('filters.search')"
            >
                <Search class="h-4 w-4" />
            </Button>

            <Sheet v-model:open="showAdvanced">
                <SheetTrigger as-child>
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        :class="{
                            'border-primary/50 bg-secondary': showAdvanced,
                        }"
                        :aria-label="$t('filters.advancedTitle')"
                    >
                        <SlidersHorizontal class="h-4 w-4" />
                    </Button>
                </SheetTrigger>

                <SheetContent
                    side="left"
                    class="flex h-full w-[320px] flex-col gap-0 border-r-border p-0 sm:w-[400px]"
                >
                    <SheetHeader class="border-b border-border/50 p-6">
                        <SheetTitle class="text-xl">{{
                            $t('filters.advancedTitle')
                        }}</SheetTitle>
                        <SheetDescription>
                            {{ $t('filters.advancedDescription') }}
                        </SheetDescription>
                    </SheetHeader>

                    <ScrollArea class="flex-1">
                        <div class="flex flex-col gap-6 p-6">
                            <div class="space-y-2.5">
                                <Label
                                    for="category"
                                    class="text-sm font-semibold"
                                    >{{ $t('filters.category') }}</Label
                                >
                                <Select id="category" v-model="form.category">
                                    <SelectTrigger>
                                        <SelectValue
                                            :placeholder="
                                                $t('filters.allCategories')
                                            "
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{{
                                            $t('filters.allCategories')
                                        }}</SelectItem>
                                        <SelectItem
                                            v-for="catKey in formattedCategories"
                                            :key="catKey.id"
                                            :value="catKey.slug"
                                        >
                                            {{ catKey.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2.5">
                                <Label class="text-sm font-semibold">{{
                                    $t('filters.listingType')
                                }}</Label>
                                <RadioGroup
                                    v-model="form.listingTypes"
                                    class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                                >
                                    <div
                                        v-for="type in listingTypes"
                                        :key="type.id"
                                        class="flex items-center space-x-2"
                                    >
                                        <RadioGroupItem
                                            :id="type.id"
                                            :value="type.id"
                                        />
                                        <Label
                                            :for="type.id"
                                            class="cursor-pointer text-sm leading-none font-normal"
                                        >
                                            {{ $t(type.labelKey) }}
                                        </Label>
                                    </div>
                                </RadioGroup>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <Label class="text-sm font-semibold">{{
                                        $t('filters.priceRange')
                                    }}</Label>
                                    <Badge
                                        variant="outline"
                                        class="font-mono font-normal"
                                    >
                                        €{{
                                            form.priceRange[0].toLocaleString(
                                                'de-DE',
                                            )
                                        }}
                                        - €{{
                                            form.priceRange[1].toLocaleString(
                                                'de-DE',
                                            )
                                        }}
                                    </Badge>
                                </div>
                                <Slider
                                    v-model="form.priceRange"
                                    :max="1000000"
                                    :step="10000"
                                    class="pt-2"
                                />
                            </div>

                            <div class="space-y-2.5">
                                <Label
                                    for="location"
                                    class="text-sm font-semibold"
                                    >{{ $t('filters.location') }}</Label
                                >
                                <Input
                                    id="location"
                                    v-model="form.location"
                                    :placeholder="
                                        $t('filters.locationPlaceholder')
                                    "
                                />
                            </div>

                            <div class="space-y-2.5">
                                <Label
                                    for="sort-by"
                                    class="text-sm font-semibold"
                                    >{{ $t('filters.sortBy') }}</Label
                                >
                                <Select id="sort-by" v-model="form.sortBy">
                                    <SelectTrigger
                                        ><SelectValue
                                    /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="opt in sortOptions"
                                            :key="opt.id"
                                            :value="opt.id"
                                        >
                                            {{ $t(opt.labelKey) }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div
                                v-if="form.listingTypes !== 'all'"
                                class="flex flex-wrap gap-2 pt-2"
                            >
                                <Badge
                                    variant="secondary"
                                    class="gap-1.5 px-2 py-1"
                                >
                                    {{
                                        $t(
                                            getListingTypeLabel(
                                                form.listingTypes,
                                            ),
                                        )
                                    }}
                                    <X
                                        class="h-3 w-3 cursor-pointer transition-colors hover:text-destructive"
                                        @click="removeType()"
                                    />
                                </Badge>
                            </div>
                        </div>
                    </ScrollArea>

                    <SheetFooter
                        class="mt-auto border-t border-border/50 bg-muted/10 p-6"
                    >
                        <Button
                            class="w-full"
                            size="lg"
                            :disabled="form.processing"
                            @click="applyFilters"
                        >
                            {{ $t('filters.apply') }}
                        </Button>
                        <Button
                            variant="outline"
                            class="flex-1"
                            @click="clearFilters"
                        >
                            {{ $t('filters.reset') }}
                        </Button>
                    </SheetFooter>
                </SheetContent>
            </Sheet>
        </form>
    </div>
</template>
