<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapedDataImage extends Model
{
    use HasFactory;

    protected $table = 'scraped_data_images';
    protected $fillable = [
        'scraped_data_id',
        'file_url',
        'file_name',
        'position'
    ];

    public function scrapedData(): BelongsTo
    {
        return $this->belongsTo(ScrapedData::class, 'scraped_data_id');
    }
}
