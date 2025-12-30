<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { useTimeAgo } from '@vueuse/core';
import { trans } from 'laravel-vue-i18n';
import {
    Bell,
    Check,
    CheckCircle2,
    Filter,
    MoreVertical,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useToast } from 'vue-toastification';

// Shared Logic
import {
    type NotificationItem,
    useNotifications,
} from '@/composables/useNotifications';
import { destroy, mark_all_read, read } from '@/routes/notifications';

// Components
import Layout from '@/components/layout/Layout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardDescription, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    notifications: {
        data: NotificationItem[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
        total: number;
    };
    unread_count: number;
}

const props = defineProps<Props>();
const toast = useToast();
const { getIcon, getIconStyles } = useNotifications();

const activeTab = ref('all');
const notificationList = computed(() => props.notifications.data);

// --- Actions ---

const markAsRead = (id: string) => {
    router.post(read.url(id), {}, { preserveScroll: true });
};

const markAllAsRead = () => {
    if (props.unread_count === 0) return;
    router.post(
        mark_all_read.url(),
        {},
        {
            preserveScroll: true,
            onSuccess: () =>
                toast.success(trans('notifications.mark_all_success')),
            onError: () => toast.error(trans('notifications.mark_all_error')),
        },
    );
};

const deleteNotification = (id: string) => {
    router.delete(destroy.url(id), {
        preserveScroll: true,
        onSuccess: () => toast.info(trans('notifications.deleted')),
    });
};

const handleCardClick = (notification: NotificationItem) => {
    const targetUrl = notification.data.url;
    if (!notification.read_at) {
        router.post(
            read.url(notification.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    if (targetUrl) window.location.href = targetUrl;
                },
            },
        );
    } else if (targetUrl) {
        window.location.href = targetUrl;
    }
};
</script>

<template>
    <Layout>
        <div class="container mx-auto max-w-4xl px-4 py-8 md:px-6">
            <div
                class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1
                        class="font-sans text-3xl font-bold tracking-tight text-foreground"
                    >
                        {{ $t('notifications.index.title') }}
                    </h1>
                    <p class="mt-1 text-muted-foreground">
                        {{
                            $t('notifications.index.subtitle', {
                                count: props.unread_count,
                            })
                        }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="markAllAsRead"
                        :disabled="props.unread_count === 0"
                        class="gap-2"
                    >
                        <CheckCircle2 class="h-4 w-4" />
                        {{ $t('notifications.mark_all_read') }}
                    </Button>
                </div>
            </div>

            <Tabs default-value="all" v-model="activeTab" class="w-full">
                <div class="mb-4 flex items-center justify-between">
                    <TabsList class="grid w-full max-w-[400px] grid-cols-2">
                        <TabsTrigger value="all">
                            {{ $t('notifications.filter.all') }}
                        </TabsTrigger>
                        <TabsTrigger value="unread" class="relative">
                            {{ $t('notifications.filter.unread') }}
                            <span
                                v-if="props.unread_count > 0"
                                class="ml-2 flex h-2 w-2 rounded-full bg-primary"
                            />
                        </TabsTrigger>
                    </TabsList>
                    <Button variant="ghost" size="icon" class="hidden md:flex">
                        <Filter class="h-4 w-4 text-muted-foreground" />
                    </Button>
                </div>

                <TabsContent value="all" class="mt-0 space-y-4">
                    <Card
                        v-if="notificationList.length === 0"
                        class="flex flex-col items-center justify-center border-dashed py-12 text-center"
                    >
                        <div class="mb-4 rounded-full bg-muted/50 p-4">
                            <Bell class="h-8 w-8 text-muted-foreground" />
                        </div>
                        <CardTitle class="text-lg font-medium">{{
                            $t('notifications.empty.title')
                        }}</CardTitle>
                        <CardDescription>{{
                            $t('notifications.empty.description')
                        }}</CardDescription>
                    </Card>

                    <div v-else class="grid gap-3">
                        <Card
                            v-for="notification in notificationList"
                            :key="notification.id"
                            class="group relative cursor-pointer border-l-4 transition-all duration-200 hover:shadow-md"
                            :class="[
                                notification.read_at
                                    ? 'border-border border-l-transparent bg-card opacity-90'
                                    : 'border-border border-l-primary bg-card shadow-sm',
                            ]"
                            @click="handleCardClick(notification)"
                        >
                            <div class="flex items-start gap-4 p-4 md:p-5">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg transition-colors"
                                    :class="
                                        getIconStyles(notification.data.type)
                                    "
                                >
                                    <component
                                        :is="getIcon(notification.data.type)"
                                        class="h-5 w-5"
                                    />
                                </div>

                                <div class="flex min-w-0 flex-1 flex-col gap-1">
                                    <div
                                        class="flex items-start justify-between gap-2"
                                    >
                                        <div class="space-y-0.5">
                                            <h4
                                                class="truncate pr-4 font-sans text-sm leading-none font-semibold"
                                                :class="{
                                                    'text-muted-foreground':
                                                        notification.read_at,
                                                }"
                                            >
                                                {{
                                                    notification.data.title ||
                                                    $t(
                                                        'notifications.default_title',
                                                    )
                                                }}
                                            </h4>
                                            <p
                                                class="line-clamp-2 text-sm leading-relaxed text-muted-foreground"
                                            >
                                                {{
                                                    notification.data
                                                        .description
                                                }}
                                            </p>
                                        </div>
                                        <div
                                            class="flex shrink-0 flex-col items-end gap-2"
                                        >
                                            <span
                                                class="text-xs whitespace-nowrap text-muted-foreground"
                                            >
                                                {{
                                                    useTimeAgo(
                                                        notification.created_at,
                                                    ).value
                                                }}
                                            </span>
                                            <div @click.stop>
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger
                                                        as-child
                                                    >
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            class="-mr-2 h-8 w-8 text-muted-foreground hover:text-foreground"
                                                        >
                                                            <MoreVertical
                                                                class="h-4 w-4"
                                                            />
                                                            <span
                                                                class="sr-only"
                                                                >{{
                                                                    $t(
                                                                        'actions.title',
                                                                    )
                                                                }}</span
                                                            >
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent
                                                        align="end"
                                                    >
                                                        <DropdownMenuItem
                                                            v-if="
                                                                !notification.read_at
                                                            "
                                                            @click="
                                                                markAsRead(
                                                                    notification.id,
                                                                )
                                                            "
                                                        >
                                                            <Check
                                                                class="mr-2 h-4 w-4"
                                                            />
                                                            {{
                                                                $t(
                                                                    'actions.mark_read',
                                                                )
                                                            }}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            class="text-destructive focus:text-destructive"
                                                            @click="
                                                                deleteNotification(
                                                                    notification.id,
                                                                )
                                                            "
                                                        >
                                                            <Trash2
                                                                class="mr-2 h-4 w-4"
                                                            />
                                                            {{
                                                                $t(
                                                                    'actions.delete',
                                                                )
                                                            }}
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Card>
                    </div>

                    <div
                        v-if="props.notifications.links.length > 3"
                        class="mt-8"
                    >
                        <div class="flex flex-wrap justify-center gap-1">
                            <template
                                v-for="(link, key) in props.notifications.links"
                                :key="key"
                            >
                                <div
                                    v-if="link.url === null"
                                    class="flex h-9 items-center justify-center rounded-md border border-input bg-transparent px-3 py-1 text-sm text-muted-foreground opacity-50"
                                    v-html="link.label"
                                />
                                <Link
                                    v-else
                                    :href="link.url"
                                    class="flex h-9 items-center justify-center rounded-md border px-3 py-1 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    :class="{
                                        'bg-primary text-primary-foreground hover:bg-primary/90 hover:text-primary-foreground':
                                            link.active,
                                        'border-input bg-background':
                                            !link.active,
                                    }"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="unread">
                    <div
                        class="flex flex-col items-center justify-center py-12 text-center text-muted-foreground"
                    >
                        <p>{{ $t('notifications.filter.unread_hint') }}</p>
                        <Button variant="link" @click="activeTab = 'all'">
                            {{ $t('notifications.filter.show_all') }}
                        </Button>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </Layout>
</template>
