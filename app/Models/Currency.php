<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';
    protected $fillable = [
        'code',
        'name',
        'symbol'
    ];

    public function retailers(): HasMany
    {
        return $this->hasMany(Retailer::class, 'currency_id', 'id');
    }
}
