<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Product;
use App\Models\ScrapedData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Retailer extends Model
{
    use HasFactory;

    protected $table = 'retailers';
    protected $fillable = [
        'title', 
        'url', 
        'currency_id', 
        'logo',
        'created_at',
        'updated_at'
    ];

    public function products(): BelongsToMany {
        return $this->belongsToMany(Product::class, 'product_retailers', 'retailer_id', 'product_id')
            ->withPivot('product_url');
    }

    public function scrapedData(): HasMany {
        return $this->hasMany(ScrapedData::class, 'retailer_id', 'id');
    }

    public function currency(): BelongsTo {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
