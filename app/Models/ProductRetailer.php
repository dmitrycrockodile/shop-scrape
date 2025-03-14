<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductRetailer extends Model
{
    use HasFactory;

    protected $table = 'product_retailers';
    protected $fillable = [
        'product_id',
        'retailer_id',
        'product_url',
        'created_at',
        'updated_at'
    ];

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function retailer(): BelongsTo {
        return $this->belongsTo(Retailer::class, 'retailer_id', 'id');
    }

    public function scrapedData(): HasMany {
        return $this->hasMany(ScrapedData::class, 'product_retailer_id', 'id');
    }
}
