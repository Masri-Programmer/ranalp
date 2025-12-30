import {
    AlertCircle,
    Check,
    Clock,
    CreditCard,
    Info,
    MessageSquare,
    Star,
} from 'lucide-vue-next';

// --- Shared Types (Single Source of Truth) ---
export interface NotificationData {
    title?: string;
    description?: string;
    type?:
        | 'info'
        | 'warning'
        | 'success'
        | 'error'
        | 'payment'
        | 'faq'
        | 'review'
        | 'new_bid';
    url?: string;
}

export interface NotificationItem {
    id: string;
    type: string;
    notifiable_type?: string;
    notifiable_id?: number;
    data: NotificationData;
    read_at: string | null;
    created_at: string;
    updated_at?: string;
}

// --- Composable ---
export function useNotifications() {
    /**
     * Get the Lucide icon component based on notification type
     */
    const getIcon = (type: string | undefined) => {
        switch (type) {
            case 'warning':
            case 'error':
                return AlertCircle;
            case 'success':
                return Check;
            case 'payment':
                return CreditCard;
            case 'faq':
                return MessageSquare;
            case 'review':
                return Star;
            case 'new_bid':
                return Clock;
            default:
                return Info;
        }
    };

    /**
     * Get the Tailwind classes for the icon container (Bg + Border + Text)
     */
    const getIconStyles = (type: string | undefined): string => {
        const base = 'border shadow-sm';
        switch (type) {
            case 'warning':
                return `${base} text-yellow-500 bg-yellow-500/10 border-yellow-500/20`;
            case 'success':
                return `${base} text-green-500 bg-green-500/10 border-green-500/20`;
            case 'payment':
                return `${base} text-green-600 bg-green-600/10 border-green-600/20`;
            case 'error':
                return `${base} text-destructive bg-destructive/10 border-destructive/20`;
            case 'faq':
                return `${base} text-blue-500 bg-blue-500/10 border-blue-500/20`;
            case 'review':
                return `${base} text-amber-500 bg-amber-500/10 border-amber-500/20`;
            case 'new_bid':
                return `${base} text-primary bg-primary/10 border-primary/20`;
            default:
                return `${base} text-primary bg-primary/10 border-primary/20`;
        }
    };

    return {
        getIcon,
        getIconStyles,
    };
}
