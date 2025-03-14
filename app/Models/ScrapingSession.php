<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScrapingSession extends Model
{
    use HasFactory;

    protected $table = 'scraping_sessions';
    protected $fillable = [
        'retailer_id',
        'created_at',
        'updated_at'
    ];
}
