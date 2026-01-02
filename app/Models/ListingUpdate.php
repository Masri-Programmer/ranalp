<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingUpdate extends Model
{
    use HasFactory;

    protected $fillable = ['listing_id', 'title', 'content', 'type'];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function getModelLabel()
    {
        return 'Update';
    }
}