<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'language',
        'title',
        'content',
        'excerpt',
        'is_ai_translated',
        'translation_confidence',
        'translated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_ai_translated' => 'boolean',
            'translation_confidence' => 'decimal:2',
            'translated_at' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function scopeForLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }
}
