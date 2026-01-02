<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">
                {{ $t('reviews.updates.latest') }}
            </h3>
        </div>

        <!-- Post Update Form (Owner Only) -->
        <div v-if="isOwner" class="rounded-lg border bg-muted/50 p-4">
            <h4 class="mb-3 text-sm font-medium">
                {{ $t('reviews.updates.post_title') }}
            </h4>
            <form @submit.prevent="handleSubmit" class="space-y-3">
                <Input
                    v-model="form.title"
                    :placeholder="$t('reviews.updates.title_placeholder')"
                />
                <Textarea
                    v-model="form.content"
                    :placeholder="$t('reviews.updates.content_placeholder')"
                />
                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing">
                        {{ $t('reviews.updates.post_button') }}
                    </Button>
                </div>
            </form>
        </div>

        <!-- Timeline -->
        <div class="relative ml-3 space-y-8 border-l border-muted pb-4">
            <div
                v-if="allUpdates.length === 0"
                class="pl-6 text-sm text-muted-foreground"
            >
                {{ $t('reviews.updates.tabs_empty') }}
            </div>

            <div
                v-for="update in allUpdates"
                :key="update.id"
                class="relative pl-6"
            >
                <!-- Dot on the line -->
                <div
                    class="absolute top-1.5 -left-[5px] h-2 w-2 rounded-full bg-primary"
                ></div>

                <div class="flex flex-col space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold">
                            {{
                                update.type === 'manual'
                                    ? update.title
                                    : $t('reviews.updates.system_update_label')
                            }}
                        </span>
                        <div class="flex items-center gap-4">
                            <span class="text-xs text-muted-foreground">
                                {{ formatDate(update.created_at) }}
                            </span>

                            <!-- Edit/Delete Actions -->
                            <div
                                v-if="isOwner && update.type === 'manual'"
                                class="flex gap-2"
                            >
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 px-2 text-xs"
                                    @click="startEdit(update)"
                                >
                                    {{ $t('actions.edit') }}
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 px-2 text-xs text-destructive hover:text-destructive"
                                    @click="deleteUpdate(update)"
                                >
                                    {{ $t('actions.delete') }}
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div v-if="editingId === update.id" class="mt-2 space-y-3">
                        <Input v-model="editForm.title" />
                        <Textarea v-model="editForm.content" />
                        <div class="flex justify-end gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="cancelEdit"
                            >
                                {{ $t('actions.cancel') }}
                            </Button>
                            <Button
                                size="sm"
                                :disabled="editForm.processing"
                                @click="saveUpdate"
                            >
                                {{ $t('actions.save') }}
                            </Button>
                        </div>
                    </div>

                    <!-- View Mode -->
                    <p
                        v-else
                        class="text-sm whitespace-pre-wrap text-foreground/80"
                    >
                        <template v-if="update.type === 'manual'">
                            {{ update.content }}
                        </template>
                        <template v-else>
                            {{ $t(update.translation_key, update.attributes) }}
                        </template>
                    </p>
                </div>
            </div>
        </div>

        <!-- Load More Button -->
        <div v-if="localNextPageUrl" class="flex justify-center pt-4">
            <Button
                variant="outline"
                :disabled="isLoadingMore"
                @click="loadMore"
            >
                <template v-if="isLoadingMore">
                    <Loader2 class="mr-2 h-4 w-4 animate-spin" />
                </template>
                {{ $t('actions.load_more') }}
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { destroy, store, update } from '@/routes/listings/updates';
import { ListingUpdate } from '@/types/listings';
import { router, useForm } from '@inertiajs/vue3';
import { formatDistanceToNow } from 'date-fns';
import { trans } from 'laravel-vue-i18n';
import { Loader2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    listingId: number;
    updates: ListingUpdate[];
    isOwner: boolean;
    nextPageUrl?: string | null;
}>();

// --- Infinite Scroll / State ---
const allUpdates = ref<ListingUpdate[]>([...props.updates]);
const localNextPageUrl = ref<string | null>(props.nextPageUrl || null);
const isLoadingMore = ref(false);

// Watch for internal resets
watch(
    () => props.updates,
    (newUpdates) => {
        if (isLoadingMore.value) return;
        allUpdates.value = [...newUpdates];
        localNextPageUrl.value = props.nextPageUrl || null;
    },
);

// --- Forms ---
const form = useForm({
    title: '',
    content: '',
});

const editingId = ref<number | null>(null);
const editForm = useForm({
    title: '',
    content: '',
});

// --- Actions ---

function handleSubmit() {
    form.post(store.url(props.listingId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
}

function startEdit(update: ListingUpdate) {
    editingId.value = update.id;
    editForm.title = update.title || '';
    editForm.content = update.content;
}

function cancelEdit() {
    editingId.value = null;
    editForm.reset();
}

function saveUpdate() {
    if (!editingId.value) return;

    editForm.put(update.url(editingId.value), {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
        },
    });
}

function deleteUpdate(update: ListingUpdate) {
    if (!confirm(trans('actions.delete_confirmation.description'))) return;

    router.delete(destroy.url(update.id), {
        preserveScroll: true,
    });
}

function loadMore() {
    if (!localNextPageUrl.value || isLoadingMore.value) return;

    isLoadingMore.value = true;

    router.get(
        localNextPageUrl.value,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            only: ['listing'],
            onSuccess: (page: any) => {
                const listing = page.props.listing;
                const newUpdates = listing.updates;
                const nextPage = listing.next_update_page_url;

                // Append unique
                const existingIds = new Set(allUpdates.value.map((u) => u.id));
                const uniqueNew = newUpdates.filter(
                    (u: ListingUpdate) => !existingIds.has(u.id),
                );

                if (uniqueNew.length > 0) {
                    allUpdates.value = [...allUpdates.value, ...uniqueNew];
                }
                localNextPageUrl.value = nextPage;
            },
            onFinish: () => {
                isLoadingMore.value = false;
            },
        },
    );
}

function formatDate(date: string) {
    return formatDistanceToNow(new Date(date), { addSuffix: true });
}
</script>
