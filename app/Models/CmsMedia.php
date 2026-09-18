<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsMedia extends Model
{
    protected $table = 'cms_media';

    protected $fillable = [
        'mediable_type', 'mediable_id', 'path', 'thumbnail_path',
        'width', 'height', 'sort_order', 'alt_text',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    public function mediable()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return asset('storage/' . $this->thumbnail_path);
    }
}
