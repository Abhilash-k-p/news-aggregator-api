<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'content', 'url', 'published_at',
        'category_id', 'source_id', 'author', 'source_article_id'
    ];

    protected static function booted(): void
    {
        static::updated(function ($article) {
            Cache::forget("article_{$article->id}");
        });

        static::deleted(function ($article) {
            Cache::forget("article_{$article->id}");
        });
    }

    /**
     * scopes
     */
    public function scopeFilterByKeyword($query, $keyword): void
    {
        if ($keyword) {
            $query->where('title', 'like', "%{$keyword}%")
                ->orWhere('content', 'like', "%{$keyword}%");
        }
    }

    public function scopeFilterByPublishedDate($query, $date): void
    {
        if ($date) {
            $query->whereDate('published_at', $date);
        }
    }

    public function scopeFilterByCategoryName($query, $category): void
    {
        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', 'like', "%{$category}%");
            });
        }
    }

    public function scopeFilterBySourceName($query, $source): void
    {
        if ($source) {
            $query->whereHas('source', function ($q) use ($source) {
                $q->where('name', 'like', "%{$source}%");
            });
        }
    }

    public function scopeFilterByAuthor($query, $author): void
    {
        $query->where('author', 'like', "%{$author}%");
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
