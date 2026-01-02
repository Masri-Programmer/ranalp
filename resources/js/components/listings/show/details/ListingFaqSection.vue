<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-semibold">
                {{ $t('listings.faq.title') }}
            </h3>
            <Button
                v-if="!showAskForm"
                @click="handleAskQuestion"
                variant="outline"
            >
                {{ $t('listings.faq.ask_question') }}
            </Button>
        </div>

        <Card v-if="showAskForm">
            <CardContent class="space-y-4 pt-6">
                <div class="space-y-2">
                    <Label>{{ $t('listings.faq.your_question') }}</Label>
                    <Textarea v-model="newQuestion" rows="2" />
                </div>
                <div class="flex justify-end gap-2">
                    <Button variant="ghost" @click="showAskForm = false">
                        {{ $t('actions.cancel') }}
                    </Button>
                    <Button @click="submitQuestion" :disabled="processing">
                        {{ $t('actions.submit') }}
                    </Button>
                </div>
            </CardContent>
        </Card>

        <Accordion type="single" collapsible class="w-full">
            <AccordionItem
                v-for="faq in faqs"
                :key="faq.id"
                :value="'item-' + faq.id"
            >
                <AccordionTrigger class="text-left hover:no-underline">
                    <div
                        class="flex w-full flex-col items-start gap-1 text-left"
                    >
                        <div
                            v-if="editingQuestionId === faq.id"
                            class="w-full pr-4"
                            @click.stop
                        >
                            <div class="space-y-2">
                                <Textarea
                                    v-model="editQuestionForm.question"
                                    rows="2"
                                />
                                <div class="flex gap-2">
                                    <Button
                                        size="sm"
                                        @click="saveQuestion(faq)"
                                    >
                                        {{ $t('actions.save') }}
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        @click="editingQuestionId = null"
                                    >
                                        {{ $t('actions.cancel') }}
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex w-full items-start justify-between"
                        >
                            <span class="font-medium">
                                {{ getTranslation(faq.question) }}
                            </span>

                            <!-- Question Actions for Asker (Non-Owner) -->
                            <div
                                v-if="
                                    currentUser &&
                                    currentUser.id === faq.user_id &&
                                    !isOwner
                                "
                                class="ml-2 flex gap-1"
                                @click.stop
                            >
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    class="h-6 w-6"
                                    @click="startEditQuestion(faq)"
                                >
                                    <Pencil class="h-3 w-3" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    class="h-6 w-6 text-destructive"
                                    @click="deleteFaq(faq)"
                                >
                                    <Trash2 class="h-3 w-3" />
                                </Button>
                            </div>

                            <!-- Question Actions for Owner -->
                            <div
                                v-if="isOwner"
                                class="ml-2 flex gap-1"
                                @click.stop
                            >
                                <!-- <Button
                                    size="icon"
                                    :variant="
                                        faq.is_visible ? 'ghost' : 'default'
                                    "
                                    class="h-6 w-6"
                                    @click="toggleVisibility(faq)"
                                    :title="
                                        faq.is_visible
                                            ? $t('actions.hide')
                                            : $t('actions.approve')
                                    "
                                >
                                    <Eye class="h-3 w-3" />
                                </Button> -->
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    class="h-6 w-6 text-destructive"
                                    @click="deleteFaq(faq)"
                                    :title="$t('actions.delete')"
                                >
                                    <Trash2 class="h-3 w-3" />
                                </Button>
                            </div>
                        </div>

                        <span
                            v-if="isOwner && !faq.is_visible"
                            class="mt-1 rounded bg-yellow-100 px-2 py-0.5 text-xs text-yellow-800"
                        >
                            {{ $t('status.pending') }}
                        </span>
                    </div>
                </AccordionTrigger>

                <AccordionContent class="space-y-4 text-muted-foreground">
                    <!-- Answer Display -->
                    <div
                        v-if="faq.answer && editingAnswerId !== faq.id"
                        class="prose dark:prose-invert"
                    >
                        {{ getTranslation(faq.answer) }}
                    </div>
                    <div
                        v-else-if="!isOwner && !faq.answer"
                        class="text-sm italic"
                    >
                        {{ $t('listings.faq.waiting_for_answer') }}
                    </div>

                    <!-- Owner Actions (Answer/Edit Answer) -->
                    <div v-if="isOwner" class="mt-2 border-t pt-2">
                        <div
                            v-if="editingAnswerId === faq.id"
                            class="space-y-3"
                        >
                            <Textarea
                                v-model="editAnswerForm.answer"
                                :placeholder="$t('listings.faq.write_answer')"
                            />
                            <div class="flex gap-2">
                                <Button size="sm" @click="saveAnswer(faq)">
                                    {{ $t('actions.save') }}
                                </Button>
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    @click="editingAnswerId = null"
                                >
                                    {{ $t('actions.cancel') }}
                                </Button>
                            </div>
                        </div>

                        <div v-else>
                            <Button
                                size="sm"
                                variant="outline"
                                @click="startEditAnswer(faq)"
                            >
                                <Pencil class="mr-1 h-3 w-3" />
                                {{
                                    faq.answer
                                        ? $t('actions.edit')
                                        : $t('listings.faq.answer')
                                }}
                            </Button>
                        </div>
                    </div>
                </AccordionContent>
            </AccordionItem>
        </Accordion>

        <div
            v-if="faqs.length === 0"
            class="py-8 text-center text-muted-foreground"
        >
            {{ $t('listings.faq.no_questions_yet') }}
        </div>
    </div>
</template>

<script setup lang="ts">
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { login } from '@/routes';
import { destroy, store, update } from '@/routes/listings/faq/index';
import { User } from '@/types';
import { ListingFaq } from '@/types/listings';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    listingId: number;
    isOwner: boolean;
    faqs: ListingFaq[];
}>();

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'de');
const currentUser = computed(() => page.props.auth.user as User);

const getTranslation = (field: any) => {
    if (typeof field === 'string') return field;
    if (!field) return '';
    // @ts-ignore
    return field[currentLocale.value] || field['en'] || Object.values(field)[0];
};

// --- Ask Question ---
const showAskForm = ref(false);
const newQuestion = ref('');
const processing = ref(false);

const handleAskQuestion = () => {
    if (currentUser.value) {
        showAskForm.value = true;
    } else {
        router.visit(login());
    }
};

const submitQuestion = () => {
    if (!newQuestion.value.trim()) return;

    processing.value = true;
    router.post(
        store.url({ listing: props.listingId }),
        {
            question: newQuestion.value,
        },
        {
            onSuccess: () => {
                showAskForm.value = false;
                newQuestion.value = '';
                processing.value = false;
            },
            onError: () => (processing.value = false),
        },
    );
};

// --- Edit Question (Asker) ---
const editingQuestionId = ref<number | null>(null);
const editQuestionForm = useForm({
    question: '',
});

const startEditQuestion = (faq: ListingFaq) => {
    editingQuestionId.value = faq.id;
    editQuestionForm.question = getTranslation(faq.question);
};

const saveQuestion = (faq: ListingFaq) => {
    editQuestionForm.patch(
        update.url({ listing: props.listingId, faq: faq.id }),
        {
            onSuccess: () => {
                editingQuestionId.value = null;
            },
        },
    );
};

const editingAnswerId = ref<number | null>(null);
const editAnswerForm = useForm({
    answer: '',
});

const startEditAnswer = (faq: ListingFaq) => {
    editingAnswerId.value = faq.id;
    editAnswerForm.answer = getTranslation(faq.answer);
};

const saveAnswer = (faq: ListingFaq) => {
    editAnswerForm.patch(
        update.url({ listing: props.listingId, faq: faq.id }),
        {
            onSuccess: () => {
                editingAnswerId.value = null;
            },
        },
    );
};

// const toggleVisibility = (faq: ListingFaq) => {
//     router.patch(update.url({ listing: props.listingId, faq: faq.id }), {
//         is_visible: !faq.is_visible,
//     });
// };

const deleteFaq = (faq: ListingFaq) => {
    router.delete(destroy.url({ listing: props.listingId, faq: faq.id }));
};
</script>
