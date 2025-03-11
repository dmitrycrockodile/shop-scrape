<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Product;
use App\Models\ScrapedData;

class Retailer extends Model
{
    use HasFactory;

    protected $table = 'retailers';
    protected $fillable = [
        'title', 
        'url', 
        'currency', 
        'logo'
    ];

    public function products(): BelongsToMany {
        return $this->belongsToMany(Product::class, 'product_retailers', 'retailer_id', 'product_id');
    }

    public function scrapedData(): HasMany {
        return $this->hasMany(ScrapedData::class, 'retailer_id', 'id');
    }
}
