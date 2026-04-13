<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_source_id',
        'category_id',
        'external_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'author',
        'image_url',
        'source_url',
        'language',
        'published_at',
        'is_translated',
        'is_featured',
        'is_approved',
        'synced_at',
        'content_fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'is_translated' => 'boolean',
            'is_featured' => 'boolean',
            'is_approved' => 'boolean',
            'published_at' => 'datetime',
            'synced_at' => 'datetime',
            'content_fetched_at' => 'datetime',
        ];
    }

    public function newsSource(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }

    public function englishTranslation(): HasOne
    {
        return $this->hasOne(ArticleTranslation::class)->where('language', 'en');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeTranslated($query)
    {
        return $query->where('is_translated', true);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (Article $article) {
            if (empty($article->slug)) {
                try {
                    $article->slug = static::generateUniqueSlug(
                        $article->title,
                        $article->news_source_id
                    );
                } catch (\Throwable) {
                    $article->slug = Str::slug($article->title, '-');
                }
            }
        });
    }

    public static function generateUniqueSlug(string $title, int $newsSourceId): string
    {
        $base = Str::slug($title, '-');
        $slug = $base;
        $counter = 1;

        while (static::where('news_source_id', $newsSourceId)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function getSlugAttribute(): ?string
    {
        return $this->attributes['slug'];
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->englishTranslation?->title ?? $this->title;
    }

    public function getDisplayContentAttribute(): ?string
    {
        return $this->englishTranslation?->content ?? $this->content;
    }

    public function getDisplayExcerptAttribute(): ?string
    {
        return $this->englishTranslation?->excerpt ?? $this->excerpt;
    }
}
