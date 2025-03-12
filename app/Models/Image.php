<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends Model
{
    use HasFactory;

    protected $table = 'images';
    protected $fillable = [ 
        'imageable_id',
        'imageable_type',
        'file_url'
    ];

    public function imageable() {
        return $this->morphTo();
    }
}
