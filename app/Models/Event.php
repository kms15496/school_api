<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Event extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'date',
        'title',
        'body',
    ];

    protected $appends = [
        'image_path',
    ];

    public function getImagePathAttribute(): ?string
    {
        return $this->getFirstMediaUrl('events') ?: null;
    }
}
