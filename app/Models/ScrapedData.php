<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\Rating;

class ScrapedData extends Model
{
    use HasFactory;

    protected $table = 'scraped_data';
    protected $fillable = [
        'product_id', 
        'retailer_id', 
        'title', 
        'description', 
        'price', 
        'stock_count', 
        'avg_rating'
    ];

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function retailer(): BelongsTo {
        return $this->belongsTo(Retailer::class, 'retailer_id', 'id');
    }

    public function ratings(): HasMany {
        return $this->hasMany(Rating::class, 'scraped_data_id', 'id');
    }
}