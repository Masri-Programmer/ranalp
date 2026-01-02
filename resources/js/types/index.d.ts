import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import { PluginOptions } from 'vue-toastification';
export interface Address {
    id: number;
    addressable_type: string;
    addressable_id: number;
    street: string;
    city: string;
    state: string | null;
    zip: string;
    country: string;
    latitude: number | null;
    longitude: number | null;
    is_primary: boolean;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface Role {
    id: number;
    name: string;
    slug: string;
    created_at: string;
    updated_at: string;
    pivot: {
        user_id: number;
        role_id: number;
    };
}

/**
 * Represents the User object. Note that it contains
 * the *full* Role and Address objects.
 */
export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    locale: string;
    currency: string;
    profile_photo_path: string | null;
    created_at: string;
    updated_at: string;
    two_factor_confirmed_at: string | null;
    address_id: number | null;
    roles: Role[];
    addresses: Address[];
}

export interface Auth {
    user: User;
    roles: string[];
    permissions: string[];
    addresses: Address[];
    listings_count: number;
    notifications: any[];
    unread_notifications_count: number;
}

export interface Roles {
    role: string;
}

export interface BreadcrumbItem {
    title: string;
    url: string;
    current?: boolean;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type ToastType = 'success' | 'error' | 'warning' | 'info' | 'default';

export interface DevDetails {
    error?: string;
    file: string;
    line: number;
    trace?: string[];
}
export interface AppNotification {
    id?: string;
    type: ToastType;
    title: string;
    message: string;
    options: PluginOptions;
    dev_details?: DevDetails | null;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    money: {
        currencies: string[];
        default: string;
    };
    sidebarOpen: boolean;
    listings: PaginatedResponse<Listing>;
    categories: Category[];
    listing: ListingData;
    locale: string;
    supportedLocales: string[];
    listingTypes: string[];
    errors: object;
    user: User;
    category: Category;
    breadcrumbs: BreadcrumbItem[];
    settings: {
        general: GeneralSettings;
    } | null;
    flash: {
        notification: AppNotification | null;
    };
};

export interface LocaleString {
    [key: string]: string;
}

export interface Category {
    id: number;
    name: LocaleString;
    slug: string;
    description: LocaleString;
    is_active: boolean;
    meta: any | null;
    parent_id: number | null;
    sort_order: number;
    type: string;
    icon: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}
export interface PaginatedResponse<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Link[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

declare global {
    var route: (name: string, params?: any, absolute?: boolean) => string;
}

export interface Link {
    url: string | null;
    label: string;
    page: number | null;
    active: boolean;
}

interface Paginator {
    current_page: number;
    data: unknown[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: Link[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

interface Filters {
    category: string | null;
    search: string | null;
    type: string | null;
    sort: string | null;
}

export interface GeneralSettings {
    site_name: string;
    site_active: boolean;
    logo_url: string | null;
    per_page: number | null;
    contact_email: string | null;
}
