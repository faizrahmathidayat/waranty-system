<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioItem extends Model
{
    use HasFactory;

    protected $table = 'cms_portfolio_items';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'category',
        'status', 'published_at', 'show_on_glosspro', 'show_on_lexent',
        'location', 'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'show_on_glosspro' => 'boolean',
        'show_on_lexent' => 'boolean',
    ];

    public function media()
    {
        return $this->morphMany(CmsMedia::class, 'mediable')->orderBy('sort_order');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('published_at', '<=', now());
    }

    public function scopeForSite($query, string $site)
    {
        return $query->where('show_on_' . $site, true);
    }
}
