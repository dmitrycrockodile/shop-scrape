<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserProduct extends Model
{
    use HasFactory;

    protected $table = 'user_products';
    protected $fillable = [
        'user_id',
        'product_id',
        'created_at',
        'updated_at'
    ];
}
