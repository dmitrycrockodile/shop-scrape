<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackSize extends Model
{
    use HasFactory;

    protected $table = 'pack_sizes';
    protected $fillable = [
        'name',
        'weight',
        'amount'
    ];

    public function products(): HasMany {
        return $this->hasMany(Product::class, 'pack_size_id', 'id');
    }
}
