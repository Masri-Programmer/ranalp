<?php

namespace App\Http\Requests\Listings;

use Carbon\Carbon;
use App\Http\Requests\BaseFormRequest;
use App\Enums\ListingType;
use Illuminate\Validation\Rule;

class StoreListingRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Helper to parse complex date arrays from frontend
        $parseDate = function ($dateInput) {
            if (is_array($dateInput) && !empty($dateInput)) {
                if (isset($dateInput['year'], $dateInput['month'], $dateInput['day'])) {
                    return "{$dateInput['year']}-{$dateInput['month']}-{$dateInput['day']}";
                }
                if (isset($dateInput[0]) && is_string($dateInput[0])) {
                    return $dateInput[0];
                }
            }
            return is_string($dateInput) ? $dateInput : null;
        };

        $expires = $parseDate($this->expires_at);
        $starts = $parseDate($this->starts_at);
        $ends = $parseDate($this->ends_at);

        // For auctions/charity actions, ensure expires_at matches ends_at if provided
        if ($ends && !$expires) {
            $expires = $ends;
        }

        $this->merge([
            'expires_at' => $expires ? Carbon::parse($expires)->toDateTimeString() : null,
            'starts_at' => $starts ? Carbon::parse($starts)->toDateTimeString() : null,
            'ends_at' => $ends ? Carbon::parse($ends)->toDateTimeString() : null,
        ]);
    }


    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userLocale = app()->getLocale();

        $rules = [
            // --- Common Data ---
            'title' => 'required|array',
            'title.' . $userLocale => 'required|string|max:255',
            'title.*' => 'nullable|string|max:255',

            'description' => 'required|array',
            'description.' . $userLocale => 'required|string',
            'description.*' => 'nullable|string',

            'category_id' => 'required|exists:categories,id',
            'address_id' => 'nullable|exists:addresses,id',
            'expires_at' => 'nullable|date|after:now',

            'type' => [
                'required',
                'string',
                Rule::in(ListingType::values()),
            ],

            // --- 2. Technical Modes ---
            // 'charity_action' can be 'auction' or 'purchase'. Others default to 'donation'.
            'mode' => 'nullable|string|in:purchase,auction,donation',

            // --- Media Data ---
            'images' => 'nullable|array',
            'images.*' => 'file|mimetypes:image/jpeg,image/png,image/webp|max:10240',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:5120',
            'videos' => 'nullable|array',
            'videos.*' => 'file|mimetypes:video/mp4,video/quicktime|max:51200',
        ];

        // --- 3. Club Validation (Donation Campaign) ---
        // Add a conditional rule: If type is donation_campaign, user must be a club.
        if ($this->input('type') === 'donation_campaign') {
            // Note: Replace 'is_club' with your actual User attribute or Role check later.
            // For now, we allow it, but this is where you would restrict it.
            /* $rules['type'][] = function ($attribute, $value, $fail) {
                if (! auth()->user()->hasRole('club')) {
                    $fail('Only verified clubs can start a Donation Campaign.');
                }
            }; 
            */
        }

        // --- 4. Type-Specific Logic ---
        $specificRules = [];
        $type = $this->input('type');

        // Infer mode if not explicitly sent (fallback)
        $mode = $this->input('mode') ?? 'donation';

        // Force mode based on strict business type rules
        if (
            in_array($type, [
                ListingType::PRIVATE_OCCASION->value,
                ListingType::DONATION_CAMPAIGN->value,
                ListingType::FOUNDERS_CREATIVES->value
            ])
        ) {
            $mode = 'donation';
        }

        switch ($mode) {
            case 'auction':
                $specificRules = [
                    'start_price' => 'required|numeric|min:0',
                    'starts_at' => 'nullable|date|after:now',
                    'ends_at' => 'required|date|after:starts_at',
                    'reserve_price' => 'nullable|numeric|gte:start_price',
                    'purchase_price' => 'nullable|numeric|gte:start_price',
                    'item_condition' => 'required|string|in:new,used',
                ];
                break;

            case 'purchase':
                $specificRules = [
                    'purchase_price' => 'required|numeric|min:0',
                    'starts_at' => 'nullable|date',
                    'item_condition' => 'required|string|in:new,used',
                ];
                break;

            case 'donation':
                $specificRules = [
                    'target' => 'required|numeric|min:1', // Ensure at least 1 currency unit
                    'is_capped' => 'boolean',
                ];
                break;
        }

        return array_merge($rules, $specificRules);
    }

    /**
     * Get the common listing data from the validated request.
     */
    public function getCommonData(): array
    {
        return $this->safe()->only([
            'title',
            'description',
            'category_id',
            'type',
            'mode',
            'expires_at',
            'address_id',
        ]);
    }


    /**
     * Get the specific listable data from the validated request.
     */
    public function getSpecificData(): array
    {
        $type = $this->input('type');
        $mode = $this->input('mode') ?? 'donation';

        // 1. Force Mapping: Business Types -> Technical Modes
        if (
            in_array($type, [
                ListingType::PRIVATE_OCCASION->value,
                ListingType::DONATION_CAMPAIGN->value,
                ListingType::FOUNDERS_CREATIVES->value
            ])
        ) {
            $mode = 'donation';
        }

        // 2. Return Data based on Mode
        return match ($mode) {
            'auction', 'purchase' => $this->safe()->only([
                'start_price',
                'starts_at',
                'ends_at',
                'reserve_price',
                'purchase_price',
                'item_condition',
            ]),

            'donation' => $this->safe()->only([
                'target',
                'is_capped'
            ]),

            default => [],
        };
    }
}