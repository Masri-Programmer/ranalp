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
                // (Assumes you added 'id' to the backend as shown in Step 1)
                if (notification.id && notification.id === lastToastId.value) {
                    return;
                }

                // 3. Update the last ID
                lastToastId.value = notification.id || null;

                const { type, message, options } = notification;

                const method =
                    type &&
                    typeof toast[type as keyof typeof toast] === 'function'
                        ? (type as keyof typeof toast)
                        : 'success';

                toast[method](message, options || {});

                // Optional: Manually clear the prop in the local state to be extra safe
                // page.props.flash.notification = null;
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
