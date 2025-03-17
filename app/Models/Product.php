<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $fillable = [
        'title',
        'description',
        'manufacturer_part_number',
        'pack_size',
        'created_at',
        'updated_at'
    ];

    public function images(): HasMany {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    public function scrapedData(): HasMany {
        return $this->hasMany(ScrapedData::class, 'product_id', 'id');
    }

    public function retailers(): BelongsToMany {
        return $this->belongsToMany(Retailer::class, 'product_retailers', 'product_id', 'retailer_id')
            ->withPivot('product_url');
    }
}
