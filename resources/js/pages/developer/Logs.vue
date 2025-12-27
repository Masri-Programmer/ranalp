<template>
    <AppLayout :title="$t('Developer Logs')">
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Developer Logs</h1>
                    <p class="text-muted-foreground">
                        View and debug Laravel application logs
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        @click="downloadLogs"
                        :disabled="!fileExists"
                    >
                        <Download class="mr-2 h-4 w-4" />
                        Download
                    </Button>
                    <Button
                        variant="destructive"
                        @click="clearLogs"
                        :disabled="!fileExists"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Clear Logs
                    </Button>
                    <Button variant="outline" @click="refreshLogs">
                        <RotateCw class="mr-2 h-4 w-4" />
                        Refresh
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <Card class="mb-6">
                <CardContent class="pt-6">
                    <div
                        class="flex flex-col gap-4 md:flex-row md:items-center"
                    >
                        <!-- Level Filter -->
                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-for="level in logLevels"
                                :key="level.value"
                                :variant="
                                    filters.level === level.value
                                        ? 'default'
                                        : 'outline'
                                "
                                size="sm"
                                @click="setFilter(level.value)"
                                :class="level.class"
                            >
                                {{ level.label }}
                            </Button>
                        </div>

                        <!-- Search -->
                        <div class="flex-1">
                            <Input
                                v-model="searchQuery"
                                placeholder="Search logs..."
                                @input="debouncedSearch"
                                class="w-full"
                            >
                                <template #prefix>
                                    <Search
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                </template>
                            </Input>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Logs Display -->
            <Card v-if="!fileExists" class="p-8 text-center">
                <AlertCircle
                    class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                />
                <h3 class="mb-2 text-lg font-semibold">No log file found</h3>
                <p class="text-muted-foreground">
                    The Laravel log file doesn't exist yet. Logs will appear
                    here once the application generates them.
                </p>
            </Card>

            <Card v-else-if="filteredLogs.length === 0" class="p-8 text-center">
                <FileText
                    class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                />
                <h3 class="mb-2 text-lg font-semibold">No logs found</h3>
                <p class="text-muted-foreground">
                    No logs match your filters. Try adjusting your search
                    criteria.
                </p>
            </Card>

            <div v-else class="space-y-2">
                <Card
                    v-for="(log, index) in filteredLogs"
                    :key="index"
                    class="overflow-hidden"
                    :class="getLogCardClass(log.level)"
                >
                    <CardContent class="p-0">
                        <div
                            class="cursor-pointer p-4 hover:bg-accent/50"
                            @click="toggleLogExpansion(index)"
                        >
                            <div class="flex items-start gap-3">
                                <!-- Level Badge -->
                                <Badge :class="getLevelBadgeClass(log.level)">
                                    {{ log.level }}
                                </Badge>

                                <!-- Log Content -->
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex items-center gap-2 text-sm text-muted-foreground"
                                    >
                                        <Clock class="h-3 w-3" />
                                        <span>{{ log.timestamp }}</span>
                                        <span class="text-xs">{{
                                            log.environment
                                        }}</span>
                                    </div>
                                    <p class="mt-1 font-mono text-sm">
                                        {{ log.message }}
                                    </p>
                                </div>

                                <!-- Expand Icon -->
                                <ChevronDown
                                    class="h-5 w-5 transition-transform"
                                    :class="{
                                        'rotate-180': expandedLogs.has(index),
                                    }"
                                />
                            </div>
                        </div>

                        <!-- Expanded Content -->
                        <div
                            v-if="expandedLogs.has(index)"
                            class="border-t bg-muted/30 p-4"
                        >
                            <pre
                                class="overflow-x-auto rounded bg-background p-4 font-mono text-xs"
                                >{{ log.full }}</pre
                            >
                            <div v-if="log.context" class="mt-2">
                                <p
                                    class="mb-1 text-xs font-semibold text-muted-foreground"
                                >
                                    Context:
                                </p>
                                <pre
                                    class="overflow-x-auto rounded bg-background p-4 font-mono text-xs"
                                    >{{ formatJSON(log.context) }}</pre
                                >
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Log Count -->
            <div
                v-if="filteredLogs.length > 0"
                class="mt-4 text-center text-sm text-muted-foreground"
            >
                Showing {{ filteredLogs.length }} log
                {{ filteredLogs.length === 1 ? 'entry' : 'entries' }}
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { logs } from '@/routes/developer';
import { clear, download } from '@/routes/developer/logs';
import { router } from '@inertiajs/vue3';
import {
    AlertCircle,
    ChevronDown,
    Clock,
    Download,
    FileText,
    RotateCw,
    Search,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface LogEntry {
    timestamp: string;
    environment: string;
    level: string;
    message: string;
    context: string | null;
    full: string;
}

interface Props {
    logs: LogEntry[];
    filters: {
        level?: string;
        search?: string;
    };
    fileExists: boolean;
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const filters = ref({
    level: props.filters.level || null,
});
const expandedLogs = ref(new Set<number>());

const logLevels = [
    { value: null, label: 'All', class: '' },
    {
        value: 'error',
        label: 'Errors',
        class: 'border-red-500 text-red-700 dark:text-red-400',
    },
    {
        value: 'warning',
        label: 'Warnings',
        class: 'border-yellow-500 text-yellow-700 dark:text-yellow-400',
    },
    {
        value: 'debug',
        label: 'Debug',
        class: 'border-blue-500 text-blue-700 dark:text-blue-400',
    },
    {
        value: 'info',
        label: 'Info',
        class: 'border-green-500 text-green-700 dark:text-green-400',
    },
];

const filteredLogs = computed(() => props.logs);

const setFilter = (level: string | null) => {
    filters.value.level = level;
    applyFilters();
};

let searchTimeout: number;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = window.setTimeout(() => {
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    router.get(
        logs.url(),
        {
            level: filters.value.level,
            search: searchQuery.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const toggleLogExpansion = (index: number) => {
    if (expandedLogs.value.has(index)) {
        expandedLogs.value.delete(index);
    } else {
        expandedLogs.value.add(index);
    }
};

const getLevelBadgeClass = (level: string) => {
    const classes: Record<string, string> = {
        ERROR: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        WARNING:
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        DEBUG: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        INFO: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    };
    return (
        classes[level] ||
        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
    );
};

const getLogCardClass = (level: string) => {
    const classes: Record<string, string> = {
        ERROR: 'border-l-4 border-l-red-500',
        WARNING: 'border-l-4 border-l-yellow-500',
        DEBUG: 'border-l-4 border-l-blue-500',
        INFO: 'border-l-4 border-l-green-500',
    };
    return classes[level] || '';
};

const formatJSON = (jsonString: string) => {
    try {
        return JSON.stringify(JSON.parse(jsonString), null, 2);
    } catch {
        return jsonString;
    }
};

const refreshLogs = () => {
    router.reload({ preserveScroll: true });
};

const clearLogs = () => {
    if (
        confirm(
            'Are you sure you want to clear all logs? This action cannot be undone.',
        )
    ) {
        router.post(clear.url());
    }
};

const downloadLogs = () => {
    window.location.href = download.url();
};
</script>
