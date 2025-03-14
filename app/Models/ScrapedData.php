<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Rating;
use App\Models\ScrapedDataImage;

class ScrapedData extends Model
{
    use HasFactory;

    protected $table = 'scraped_data';
    protected $fillable = [
        'product_retailer_id', 
        'title', 
        'description', 
        'price', 
        'stock_count', 
        'avg_rating',
        'created_at',
        'updated_at'
    ];

    public function productRetailer(): BelongsTo {
        return $this->belongsTo(ProductRetailer::class, 'product_retailer_id', 'id');
    }

    public function product(): BelongsTo {
        return $this->productRetailer->product;
    }

    public function retailer(): BelongsTo {
        return $this->productRetailer->retailer;
    }

    public function ratings(): HasMany {
        return $this->hasMany(Rating::class, 'scraped_data_id', 'id');
    }

    public function images(): HasMany {
        return $this->hasMany(ScrapedDataImage::class, 'scraped_data_id', 'id');
    }
}