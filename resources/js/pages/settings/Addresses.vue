<script setup lang="ts">
import AddressController from '@/actions/App/Http/Controllers/Settings/AddressController';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import Layout from '@/components/layout/Layout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card'; // Removed unnecessary CardHeader/Title for custom layout
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { setPrimary as setPrimaryAddress } from '@/routes/addresses';
import { type Address } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { CheckCircle2, MapPin, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    addresses: Address[];
}

import AddressAutocomplete, {
    type AddressResult,
} from '@/components/AddressAutocomplete.vue';
import { useForm } from '@inertiajs/vue3';

defineProps<Props>();

const isDialogOpen = ref(false);
const editingAddress = ref<Address | null>(null);

const form = useForm({
    street: '',
    city: '',
    state: '',
    zip: '',
    country: '',
    is_primary: false,
});

const openCreateDialog = () => {
    editingAddress.value = null;
    form.reset();
    isDialogOpen.value = true;
};

const openEditDialog = (address: Address) => {
    editingAddress.value = address;
    form.street = address.street;
    form.city = address.city;
    form.state = address.state || '';
    form.zip = address.zip;
    form.country = address.country;
    form.is_primary = address.is_primary;
    isDialogOpen.value = true;
};

const handleAddressSelected = (result: AddressResult) => {
    form.street = `${result.route} ${result.street_number}`.trim();
    form.city = result.locality;
    form.state = result.administrative_area_level_1;
    form.zip = result.postal_code;
    form.country = result.country;
};

const submitForm = () => {
    if (editingAddress.value) {
        form.put(
            AddressController.update.url({ address: editingAddress.value.id }),
            {
                onSuccess: () => (isDialogOpen.value = false),
            },
        );
    } else {
        form.post(AddressController.store.url(), {
            onSuccess: () => (isDialogOpen.value = false),
        });
    }
};

const deleteAddress = (address: Address) => {
    if (confirm(trans('address.confirm_delete'))) {
        router.delete(AddressController.destroy.url({ address: address.id }));
    }
};

const setPrimary = (address: Address) => {
    router.patch(setPrimaryAddress({ address: address.id }));
};
</script>

<template>
    <Layout>
        <Head :title="$t('address.title')" />
        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <div
                    class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                >
                    <HeadingSmall
                        :title="$t('address.title')"
                        :description="$t('address.description')"
                    />
                    <Button
                        @click="openCreateDialog"
                        class="shrink-0 shadow-sm"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        {{ $t('address.add_new') }}
                    </Button>
                </div>

                <div
                    v-if="addresses.length === 0"
                    class="flex animate-in flex-col items-center justify-center rounded-xl border-2 border-dashed bg-muted/30 py-16 text-center duration-300 zoom-in-95 fade-in"
                >
                    <div class="mb-3 rounded-full bg-muted p-3">
                        <MapPin class="h-6 w-6 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-semibold">
                        {{ $t('address.no_addresses') }}
                    </h3>
                    <p class="mt-1 max-w-sm text-sm text-muted-foreground">
                        {{ $t('address.description') }}
                    </p>
                    <Button
                        variant="outline"
                        class="mt-4"
                        @click="openCreateDialog"
                    >
                        {{ $t('address.add_new') }}
                    </Button>
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="address in addresses"
                        :key="address.id"
                        class="group relative overflow-hidden transition-all duration-200 hover:border-primary/50 hover:shadow-md"
                        :class="{
                            'border-primary bg-primary/5 ring-1 ring-primary/20':
                                address.is_primary,
                            'bg-card': !address.is_primary,
                        }"
                    >
                        <div
                            v-if="address.is_primary"
                            class="absolute top-0 bottom-0 left-0 w-1 bg-primary"
                        ></div>

                        <CardContent
                            class="flex h-full flex-col justify-between p-5"
                        >
                            <div class="space-y-3">
                                <div class="flex items-start justify-between">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="h-5">
                                            <Badge
                                                v-if="address.is_primary"
                                                variant="secondary"
                                                class="border-primary/20 bg-background/80 text-[10px] font-medium text-primary shadow-sm backdrop-blur-sm"
                                            >
                                                {{ $t('address.primary') }}
                                            </Badge>
                                        </div>

                                        <h3
                                            class="text-base leading-tight font-semibold text-foreground"
                                        >
                                            {{ address.street }}
                                        </h3>
                                    </div>

                                    <div class="-mr-2 flex">
                                        <Button
                                            v-if="!address.is_primary"
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-muted-foreground transition-colors hover:bg-primary/10 hover:text-primary"
                                            @click="setPrimary(address)"
                                            :title="$t('address.set_primary')"
                                        >
                                            <CheckCircle2 class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                            @click="openEditDialog(address)"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                            @click="deleteAddress(address)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>

                                <div
                                    class="text-sm leading-relaxed text-muted-foreground"
                                >
                                    <p>
                                        {{ address.city
                                        }}{{
                                            address.state
                                                ? ', ' + address.state
                                                : ''
                                        }}
                                    </p>
                                    <p>{{ address.zip }}</p>
                                    <p
                                        class="mt-1 font-medium text-foreground/80"
                                    >
                                        {{ address.country }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                <DialogContent class="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>{{
                            editingAddress
                                ? $t('address.edit')
                                : $t('address.add_new')
                        }}</DialogTitle>
                        <DialogDescription>
                            {{ $t('address.description') }}
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="street">{{
                                $t('address.street')
                            }}</Label>
                            <AddressAutocomplete
                                id="street"
                                :model-value="form.street"
                                @update:model-value="form.street = $event"
                                @address-selected="handleAddressSelected"
                                :placeholder="$t('address.street')"
                                required
                            />
                            <InputError :message="form.errors.street" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="city">{{
                                    $t('address.city')
                                }}</Label>
                                <Input id="city" v-model="form.city" required />
                                <InputError :message="form.errors.city" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="state">{{
                                    $t('address.state')
                                }}</Label>
                                <Input
                                    id="state"
                                    v-model="form.state"
                                    required
                                />
                                <InputError :message="form.errors.state" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="zip">{{ $t('address.zip') }}</Label>
                                <Input id="zip" v-model="form.zip" required />
                                <InputError :message="form.errors.zip" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="country">{{
                                    $t('address.country')
                                }}</Label>
                                <Input
                                    id="country"
                                    v-model="form.country"
                                    required
                                />
                                <InputError :message="form.errors.country" />
                            </div>
                        </div>

                        <div
                            class="flex items-center space-x-2 rounded-lg border bg-muted/20 p-3"
                        >
                            <Checkbox
                                id="is_primary"
                                :checked="form.is_primary"
                                @update:checked="
                                    (val: boolean) => (form.is_primary = val)
                                "
                            />
                            <Label
                                for="is_primary"
                                class="flex cursor-pointer flex-col gap-1 text-sm leading-none font-medium"
                            >
                                <span>{{ $t('address.is_primary') }}</span>
                                <!-- <span
                                    class="text-xs font-normal text-muted-foreground"
                                    >{{ $t('address.primary_notice') }}</span
                                > -->
                            </Label>
                        </div>

                        <div class="flex justify-end space-x-2 pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                @click="isDialogOpen = false"
                            >
                                {{ $t('address.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ $t('address.save') }}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </SettingsLayout>
    </Layout>
</template>
