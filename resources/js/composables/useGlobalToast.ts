// resources/js/Composables/useToast.ts
import { AppPageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue'; // Import ref
import { useToast as useVueToast } from 'vue-toastification';

export function useGlobalToast() {
    const page = usePage<AppPageProps>();
    const toast = useVueToast();

    // Track the ID of the last displayed toast
    const lastToastId = ref<string | null>(null);

    const enableGlobalHandling = () => {
        watch(
            () => page.props?.flash?.notification,
            (notification) => {
                // 1. Check if notification exists
                if (!notification) return;

                // 2. PREVENT DUPLICATE: Check if we already showed this ID
                if (notification.id && notification.id === lastToastId.value) {
                    return;
                }

                lastToastId.value = notification.id || null;

                const { type, message, options } = notification;

                const method =
                    type &&
                    typeof toast[type as keyof typeof toast] === 'function'
                        ? (type as keyof typeof toast)
                        : 'success';

                toast[method](message, options || {});

                page.props.flash.notification = null;
            },
            {
                deep: true,
                immediate: true,
            },
        );
    };

    return {
        enableGlobalHandling,
        toast,
    };
}
