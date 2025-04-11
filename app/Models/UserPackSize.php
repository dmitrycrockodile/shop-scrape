<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPackSize extends Model
{
    use HasFactory;

    protected $table = 'user_pack_sizes';
    protected $fillable = [
        'user_id',
        'pack_size_id',
        'created_at',
        'updated_at'
    ];
}
