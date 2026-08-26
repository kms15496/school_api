<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Announcement extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = ['id'];
    protected $casts = [
        'class_id' => 'array',
        'date' => 'date',
    ];

    protected $appends = [
        'image_path',
        'image_paths',
    ];

    public function getImagePathAttribute(): ?string
    {
        return $this->getFirstMediaUrl('announcements') ?: null;
    }

    public function getImagePathsAttribute(): array
    {
        return $this->getMedia('announcements')
            ->map(fn ($media) => $media->getUrl())
            ->values()
            ->all();
    }
}
