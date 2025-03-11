<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ScrapedData;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'ratings';
    protected $fillable = [
        'scraped_data_id', 
        'one_star', 
        'two_stars', 
        'three_stars',
        'four_stars',
        'five_stars'
    ];

    public function scrapedData(): BelongsTo {
        return $this->belongsTo(ScrapedData::class, 'scraped_data_id', 'id');
    }
}
