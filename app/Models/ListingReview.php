<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ListingReview extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'listing_reviews';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'listing_id',
        'user_id',
        'parent_id',
        'body',
        'rating',
    ];

    /**
     * Get the listing that this review belongs to.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Get the user who wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent review (if this is a reply).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ListingReview::class, 'parent_id');
    }

    /**
     * Get the replies for this review.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ListingReview::class, 'parent_id');
    }

    /**
     * Get all of the review's media.
     * A review can have attached images or videos.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}
