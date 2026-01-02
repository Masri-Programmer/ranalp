import { Address, Category, LocaleString, User } from './../index.d';

// --- Global / Helper Types ---

export type TransactionStatus = 'pending' | 'completed' | 'failed' | 'refunded';
export type BidStatus = 'active' | 'retracted' | 'outbid' | 'won';

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginationLink[];
}

// --- Specific Metadata Types ---

export type PurchaseMetadata = {
    snapshot_price: number;
    product_name: string;
    condition: string | null;
};

export type DonationMetadata = {
    campaign_title: string;
    donor_note?: string;
};

export type InvestmentMetadata = {
    share_price_at_booking: number;
    project_name: string;
};

// --- Models ---

export interface Transaction {
    id: number;
    uuid: string;
    user_id: number;

    payable_type:
        | 'App\\Models\\InvestmentListing'
        | 'App\\Models\\PurchaseListing' // Assuming this exists based on your previous TS
        | 'App\\Models\\AuctionListing'
        | 'App\\Models\\DonationListing';

    payable_id: number;

    // Polymorphic relation
    payable?:
        | InvestmentListable
        | PurchaseListable
        | AuctionListable
        | DonationListable;

    type: 'purchase' | 'donation' | 'investment' | 'auction_win';

    amount: number;
    fee: number;
    currency: string;
    quantity: number | null;
    payment_method: string | null;
    transaction_ref: string | null;

    metadata: PurchaseMetadata | DonationMetadata | InvestmentMetadata | any;

    status: TransactionStatus;
    paid_at: string | null;
    created_at: string;
    updated_at: string;

    user?: User;
}

export interface Bid {
    id: number;
    user_id: number;
    listing_id: number;
    amount: number;
    status: BidStatus;
    ip_address?: string;
    created_at: string;
    updated_at: string;

    // Relationships
    user?: User;
    listing?: Listing;
}

export interface ListingUpdate {
    id: number;
    listing_id: number;
    title: string | null;
    content: string;
    created_at: string;
    updated_at: string;
}

export interface Listing {
    id: number;
    uuid: string;
    user_id: number;
    category_id: number;
    address_id: number | null;

    // Core fields
    title: LocaleString;
    slug: string;
    description: LocaleString;
    type: 'private' | 'creative' | 'charity' | 'purchase' | string | null;
    status: string;
    currency: string; // Added from Listing.php
    visibility: string; // Added from Listing.php

    // Stats & Counters
    views_count: number;
    likes_count: number;
    average_rating: number;
    reviews_count: number;
    comments_count: number;
    bids_count?: number; // Loaded via withCount in controller

    // Flags & Dates
    is_featured: boolean;
    published_at: string | null;
    expires_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;

    meta: any | null;

    // Polymorphic Types
    listable_type:
        | 'App\\Models\\InvestmentListing'
        | 'App\\Models\\PurchaseListing'
        | 'App\\Models\\AuctionListing'
        | 'App\\Models\\DonationListing';

    listable_id: number;

    // Polymorphic Relationship Data
    listable:
        | InvestmentListable
        | PurchaseListable
        | AuctionListable
        | DonationListable;

    // Relationships
    user: User;
    category: Category;
    address: Address | null;

    // Media (Transformed in Controller)
    media: ListingMediaCollection;
    image_url?: string; // Often appended in lists

    // Accessors & Appended Data
    price?: number | string | null; // getPriceAttribute
    is_liked_by_current_user: boolean;
    is_expired: boolean;

    // Loaded Relationships (Optional based on context)
    faqs?: ListingFaq[];
    updates?: ListingUpdate[];
    bids?: Bid[];

    // In 'show' method, reviews are mapped specially,
    // but in general usage it might be a relationship.
    reviews?: Review[];
    next_page_url?: string | null;
}

// --- Listable Sub-Types ---

export interface InvestmentListable {
    id: number;
    investment_goal: number;
    minimum_investment: number;
    shares_offered: number;
    share_price: number;
    raised: number;
    investors_count: number;
    created_at: string | null;
    updated_at: string | null;
    transactions_count?: number;
}

export interface PurchaseListable {
    id: number;
    price: number | string;
    quantity: number;
    condition: string | null;
    created_at: string;
    updated_at: string;
    transactions_count?: number;
}

export interface AuctionListable {
    id: number;
    start_price: number;
    reserve_price: number | null;
    purchase_price: number | null;
    current_bid: number | null;
    item_condition: string | null; // Added from AuctionListing.php
    starts_at: string | null;
    ends_at: string;
    transactions_count?: number;
}

export interface DonationListable {
    id: number;
    target: number; // Cast decimal:2 usually results in string/number
    raised: number; // Changed from raised to match DonationListing.php
    donors_count: number;
    is_capped: boolean;
    requires_verification: boolean; // Added from DonationListing.php
    progress_percent?: number; // Added from getProgressPercentAttribute
    created_at: string;
    updated_at: string;
    transactions_count?: number;
}

// --- Media & Support Types ---

export interface ListingMediaCollection {
    images: ListingImage[];
    videos: ListingVideo[];
    documents: ListingDocument[];
}

export interface ListingImage {
    id: number;
    url: string;
    thumbnail: string;
    mime_type: string;
}

export interface ListingVideo {
    id: number;
    url: string;
    mime_type: string;
}

export interface ListingDocument {
    id: number;
    url: string;
    file_name: string;
    size: string;
}

export interface ListingFaq {
    id: number;
    listing_id: number;
    user_id: number;
    question: LocaleString;
    answer: LocaleString | null;
    is_visible: boolean;
    created_at: string;
    updated_at: string;
    user?: { id: number; name: string };
}

// --- Review Types ---

export interface ReviewUser {
    id: number;
    name: string;
    profile_photo_url: string | '';
    // is_verified is not explicitly in the transformed array in controller, but safe to keep if User model has it
    is_verified?: boolean;
}

export interface Review {
    id: number;
    rating: number;
    body: string;
    created_at: string;
    // These are appended in the ListingController::show transformation
    time_ago: string;
    can_edit: boolean;
    user: ReviewUser;
}

// --- Inertia Page Props ---

export interface PageProps {
    listing: Listing & {
        reviews: Review[];
        next_page_url?: string | null;
    };

    listings?: PaginatedData<Listing>;

    transactions?: Transaction[];

    filters?: {
        search?: string;
        category?: string;
        types?: string;
        min_price?: number;
        max_price?: number;
        city?: string;
        sort?: string;
    };

    locale: 'en' | 'de' | 'ar';
    auth: {
        user: User | null;
    };
    category?: Category;

    // Flash messages
    flash?: {
        success?: string;
        error?: string;
    };
}
