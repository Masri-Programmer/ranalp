<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
    type NotificationItem,
    useNotifications,
} from '@/composables/useNotifications';
import { index, mark_all_read, read } from '@/routes/notifications';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useIntervalFn, useTimeAgo } from '@vueuse/core';
import { Bell, Clock } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const { getIcon, getIconStyles } = useNotifications();

// --- 1. Limit & Count Logic ---
// We access the raw array from Inertia
const rawNotifications = computed<NotificationItem[]>(
    () => page.props.auth?.notifications || [],
);

// We slice it to show max 10 in the dropdown (improves performance if backend sends many)
const items = computed(() => rawNotifications.value.slice(0, 10));

// Count comes directly from backend count
const unreadCountDisplay = computed(
    () => page.props.auth?.unread_notifications_count || 0,
);

const hasMore = computed(() => rawNotifications.value.length > 10);

// --- 2. Auto-Polling (Fixes "Refresh" Issue) ---
// Polls the server every 15 seconds to update 'auth' prop (notifications)
// "only" ensures we don't reload the whole page data, just the user/notifications part
useIntervalFn(() => {
    router.reload({
        only: ['auth'],
        preserveScroll: true,
        preserveState: true,
    });
}, 15000);

// --- 3. Actions ---
const handleNotificationClick = (notification: NotificationItem) => {
    const targetUrl = notification.data.url;

    if (!notification.read_at) {
        router.post(
            read.url(notification.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    if (targetUrl) {
                        window.location.href = targetUrl;
                    }
                },
            },
        );
    } else if (targetUrl) {
        router.visit(targetUrl);
    }
};

const markAllAsRead = () => {
    router.post(mark_all_read.url(), {}, { preserveScroll: true });
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                class="relative h-9 w-9 rounded-full focus-visible:ring-offset-0"
            >
                <Bell class="h-5 w-5 text-foreground" />
                <span class="sr-only">{{ $t('notifications.toggle') }}</span>

                <transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform scale-0 opacity-0"
                    enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="transform scale-100 opacity-100"
                    leave-to-class="transform scale-0 opacity-0"
                >
                    <span
                        v-if="unreadCountDisplay > 0"
                        class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-destructive text-[10px] font-bold text-destructive-foreground shadow-sm"
                    >
                        {{
                            unreadCountDisplay > 99 ? '99+' : unreadCountDisplay
                        }}
                    </span>
                </transition>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent class="w-80 sm:w-96" align="end">
            <DropdownMenuLabel
                class="flex items-center justify-between py-3 font-sans"
            >
                <span class="text-base font-semibold">
                    {{ $t('notifications.title') }}
                </span>
                <Button
                    v-if="unreadCountDisplay > 0"
                    variant="ghost"
                    size="sm"
                    class="h-auto px-2 text-xs text-muted-foreground hover:text-primary"
                    @click.stop="markAllAsRead"
                >
                    {{ $t('notifications.mark_all_read') }}
                </Button>
            </DropdownMenuLabel>

            <DropdownMenuSeparator />

            <ScrollArea class="h-[300px]">
                <div
                    v-if="items.length === 0"
                    class="flex flex-col items-center justify-center py-8 text-center"
                >
                    <Bell class="mb-2 h-8 w-8 text-muted-foreground/30" />
                    <p class="text-sm text-muted-foreground">
                        {{ $t('notifications.empty') }}
                    </p>
                </div>

                <div v-else class="flex flex-col gap-1 p-1">
                    <DropdownMenuItem
                        v-for="item in items"
                        :key="item.id"
                        class="flex cursor-pointer items-start gap-3 rounded-md p-3 transition-colors focus:bg-accent focus:text-accent-foreground"
                        :class="{ 'bg-muted/40': item.read_at }"
                        @click="handleNotificationClick(item)"
                    >
                        <div
                            class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                            :class="getIconStyles(item.data.type)"
                        >
                            <component
                                :is="getIcon(item.data.type)"
                                class="h-4 w-4"
                            />
                        </div>

                        <div class="flex flex-1 flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm leading-none font-medium"
                                    :class="{
                                        'font-semibold text-foreground':
                                            !item.read_at,
                                        'text-muted-foreground': item.read_at,
                                    }"
                                >
                                    {{
                                        item.data.title ||
                                        $t('notifications.default_title')
                                    }}
                                </span>
                                <span
                                    class="flex h-2 w-2 shrink-0 rounded-full bg-primary"
                                    v-if="!item.read_at"
                                ></span>
                            </div>

                            <p
                                class="line-clamp-2 text-xs text-muted-foreground"
                            >
                                {{ item.data.description }}
                            </p>

                            <div
                                class="mt-1 flex items-center gap-1 text-[10px] text-muted-foreground"
                            >
                                <Clock class="h-3 w-3" />
                                <span>{{
                                    useTimeAgo(item.created_at).value
                                }}</span>
                            </div>
                        </div>
                    </DropdownMenuItem>

                    <!-- <div
                        v-if="hasMore"
                        class="py-2 text-center text-xs text-muted-foreground italic"
                    >
                        {{ rawNotifications.length - 10 }} more notifications...
                    </div> -->
                </div>
            </ScrollArea>

            <DropdownMenuSeparator />

            <div class="p-2">
                <Button
                    variant="ghost"
                    class="w-full justify-center text-xs text-muted-foreground hover:text-foreground"
                    as-child
                >
                    <Link :href="index.url()">
                        {{ $t('notifications.view_all') }}
                    </Link>
                </Button>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
