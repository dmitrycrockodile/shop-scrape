<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScrapingSession extends Model
{
    use HasFactory;

    protected $table = 'scraping_sessions';
    protected $fillable = [
        'retailer_id',
        'created_at',
        'updated_at'
    ];

    public function scrapedData(): HasMany
    {
        return $this->hasMany(ScrapedData::class, 'scraping_session_id', 'id');
    }

    public function retailer(): BelongsTo
    {
        return $this->belongsTo(Retailer::class, 'retailer_id', 'id');
    }
}
