<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductRetailer extends Model
{
    use HasFactory;

    protected $table = 'product_retailers';
    protected $fillable = [
        'product_id',
        'retailer_id'
    ];
}
