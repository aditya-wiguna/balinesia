<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleTranslation;
use Illuminate\Support\Facades\Log;
use OpenAI;

class TranslationService
{
    private $client = null;

    public function __construct()
    {
        $apiKey = config('services.openai.api_key');
        if ($apiKey) {
            $this->client = OpenAI::client($apiKey);
        }
    }

    public function translateArticle(Article $article): ?ArticleTranslation
    {
        if ($article->language === 'en') {
            return null;
        }

        if (! $this->client) {
            Log::warning('OpenAI client not configured, skipping translation');

            return null;
        }

        if (empty($article->title) && empty($article->content)) {
            return null;
        }

        try {
            $titleEn = $this->translateText($article->title, 'Indonesian to English');
            $contentEn = $article->content
                ? $this->translateText($article->content, 'Indonesian to English')
                : null;
            $excerptEn = $article->excerpt
                ? $this->translateText($article->excerpt, 'Indonesian to English')
                : null;

            $translation = ArticleTranslation::updateOrCreate(
                [
                    'article_id' => $article->id,
                    'language' => 'en',
                ],
                [
                    'title' => $titleEn,
                    'content' => $contentEn,
                    'excerpt' => $excerptEn,
                    'is_ai_translated' => true,
                    'translation_confidence' => 0.95,
                    'translated_at' => now(),
                ]
            );

            $article->update(['is_translated' => true]);

            return $translation;
        } catch (\Exception $e) {
            Log::error("Translation failed for article {$article->id}: {$e->getMessage()}");

            return null;
        }
    }

    private function translateText(string $text, string $instruction): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $response = $this->client->chat([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a professional translator. {$instruction}. Preserve HTML formatting tags like <p>, <br>, <strong>, <em>, <a>, <ul>, <ol>, <li> in your translation. Only translate the text content, not the HTML tags themselves.",
                ],
                [
                    'role' => 'user',
                    'content' => "Translate the following text to English:\n\n{$text}",
                ],
            ],
            'temperature' => 0.3,
            'max_tokens' => 4000,
        ]);

        return trim($response->choices[0]->message->content ?? $text);
    }

    public function isConfigured(): bool
    {
        return $this->client !== null;
    }
}
