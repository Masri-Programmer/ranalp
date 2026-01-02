<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const listingTypes = computed(() => page.props.listingTypes as string[]);

const props = defineProps<{
    modelValue: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const selectedType = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});
</script>

<template>
    <div class="space-y-4">
        <Label class="text-base font-semibold">
            {{ $t('createListing.sections.type') }}
        </Label>
        <RadioGroup
            v-model="selectedType"
            :disabled="disabled"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4"
        >
            <Label
                v-for="type in listingTypes"
                :key="type"
                class="flex cursor-pointer flex-col items-center justify-center rounded-md border-2 border-muted bg-popover p-4 transition-all hover:bg-accent hover:text-accent-foreground [&:has([data-state=checked])]:border-primary"
            >
                <RadioGroupItem :value="type" class="sr-only" />
                <span class="mb-1 text-center font-semibold">
                    {{ $t(`createListing.types.${type}.title`) }}
                </span>
                <span
                    class="text-center text-xs leading-snug text-muted-foreground"
                >
                    {{ $t(`createListing.types.${type}.description`) }}
                </span>
            </Label>
        </RadioGroup>
    </div>
</template>
