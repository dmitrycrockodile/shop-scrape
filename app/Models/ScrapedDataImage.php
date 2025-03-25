<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function scrapedData()
    {
        return $this->belongsTo(ScrapedData::class, 'scraped_data_id');
    }
}
