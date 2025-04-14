<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserRetailer extends Model
{
    use HasFactory;

    protected $table = 'user_retailers';
    protected $fillable = [
        'retailer_id',
        'created_at',
        'updated_at'
    ];
}
