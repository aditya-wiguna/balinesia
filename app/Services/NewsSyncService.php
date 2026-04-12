<?php

namespace App\Services;

use App\Models\Article;
use App\Models\NewsSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsSyncService
{
    public function __construct(
        private readonly TranslationService $translationService
    ) {}

    public function syncSource(NewsSource $source): int
    {
        if (! $source->is_active) {
            return 0;
        }

        $synced = 0;
        $endpoint = $source->api_endpoint ?? $source->url.'/wp-json/wp/v2/posts';

        try {
            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->retry(3, 1000)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get($endpoint, [
                    'per_page' => 20,
                    '_embed' => true,
                ]);

            if ($response->successful()) {
                $posts = $response->json();
                foreach ($posts as $post) {
                    if ($this->syncArticle($source, $post)) {
                        $synced++;
                    }
                }
            } else {
                $bodyPreview = substr($response->body(), 0, 200);
                Log::warning("Failed to sync from {$source->name}: HTTP {$response->status()} — {$bodyPreview}");
            }
        } catch (\Exception $e) {
            Log::error("Exception syncing {$source->name}: {$e->getMessage()}");
        }

        return $synced;
    }

    private function syncArticle(NewsSource $source, array $post): bool
    {
        $externalId = (string) ($post['id'] ?? null);
        if (! $externalId) {
            return false;
        }

        $exists = Article::where('news_source_id', $source->id)
            ->where('external_id', $externalId)
            ->exists();

        if ($exists) {
            return false;
        }

        $title = $post['title']['rendered'] ?? 'Untitled';
        $content = $this->cleanHtml($post['content']['rendered'] ?? '');
        $excerpt = $this->cleanHtml($post['excerpt']['rendered'] ?? '');
        $author = $post['_embedded']['author'][0]['name'] ?? 'Unknown';
        $imageUrl = $this->extractImageUrl($post);
        $publishedAt = isset($post['date']) ? now()->parse($post['date']) : null;
        $slug = Article::generateUniqueSlug($title, $source->id);

        $article = Article::create([
            'news_source_id' => $source->id,
            'external_id' => $externalId,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'author' => $author,
            'image_url' => $imageUrl,
            'source_url' => $post['link'] ?? $source->url,
            'language' => $source->language,
            'published_at' => $publishedAt,
            'synced_at' => now(),
            'is_approved' => true,
        ]);

        $this->translationService->translateArticle($article);

        return true;
    }

    private function cleanHtml(string $html): string
    {
        return strip_tags($html, '<p><br><strong><em><a><ul><ol><li>');
    }

    /**
     * Extract image URL from a WordPress post, trying multiple sources in order:
     * 1. _embedded['wp:featuredmedia'] (standard WordPress REST API with _embed)
     * 2. yoast_head_json.og_image (balinews.id and other Yoast-powered sites)
     * 3. Parse og:image meta tag from raw yoast_head HTML as last resort
     */
    private function extractImageUrl(array $post): ?string
    {
        // Standard WordPress _embed
        if (! empty($post['_embedded']['wp:featuredmedia'][0]['source_url'])) {
            return $post['_embedded']['wp:featuredmedia'][0]['source_url'];
        }

        // Yoast SEO Open Graph image (used by balinews.id)
        $ogImages = $post['yoast_head_json']['og_image'] ?? [];
        if (! empty($ogImages[0]['url'])) {
            return $ogImages[0]['url'];
        }

        // Fallback: parse og:image from raw yoast_head HTML
        if (! empty($post['yoast_head'])) {
            if (preg_match('/<meta property="og:image" content="([^"]+)"/', $post['yoast_head'], $m)) {
                return $m[1];
            }
        }

        return null;
    }

    public function syncAllSources(): int
    {
        $total = 0;
        $sources = NewsSource::active()->get();

        foreach ($sources as $source) {
            $total += $this->syncSource($source);
        }

        return $total;
    }
}
