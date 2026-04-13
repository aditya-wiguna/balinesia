<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArticleContentFetcher
{
    private const TIMEOUT_SECONDS = 30;

    private const CONNECT_TIMEOUT = 10;

    private const CACHE_TTL_SECONDS = 86400;

    private const DEFAULT_SELECTORS = [
        'article' => [
            'article[itemprop=articleBody]',
            'article.entry-content',
            'article.post-content',
            'article.content',
            '.article-body',
            '.entry-content',
            '.post-content',
            '.article-content',
            '.single-content',
            '.post-body',
            'article',
            '.content',
            'main',
        ],
        'title' => [
            'h1.entry-title',
            'h1.post-title',
            'h1.article-title',
            'h1[itemprop=headline]',
            '.entry-title',
            '.post-title',
            'article h1',
            'h1',
        ],
        'author' => [
            'span[itemprop=author]',
            '.author-name',
            '.byline',
            '.post-author',
            '.article-author',
            '[rel=author]',
        ],
        'published_time' => [
            'time[itemprop=datePublished]',
            'time.entry-date',
            '.post-date',
            '.published',
        ],
    ];

    public function fetchAndSave(Article $article): bool
    {
        if (empty($article->source_url)) {
            Log::warning("Article {$article->id} has no source_url, skipping content fetch.");

            return false;
        }

        $cacheKey = "article_content:{$article->id}";
        if (Cache::has($cacheKey)) {
            Log::debug("Article {$article->id} content already cached, skipping.");

            return true;
        }

        $html = $this->fetchHtml($article->source_url);
        if (! $html) {
            return false;
        }

        $parsed = $this->parseArticle($html, $article->source_url);

        $updateData = [];

        if (! empty($parsed['content'])) {
            $updateData['content'] = $parsed['content'];
        }

        if (! empty($parsed['excerpt']) && empty($article->excerpt)) {
            $updateData['excerpt'] = $parsed['excerpt'];
        }

        if (! empty($parsed['author']) && empty($article->author)) {
            $updateData['author'] = $parsed['author'];
        }

        if (! empty($parsed['published_at']) && empty($article->published_at)) {
            $updateData['published_at'] = $parsed['published_at'];
        }

        if (! empty($updateData)) {
            $article->update($updateData);
        }

        $article->update(['content_fetched_at' => now()]);
        Cache::put($cacheKey, true, self::CACHE_TTL_SECONDS);

        return true;
    }

    public function fetchHtml(string $url): ?string
    {
        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->connectTimeout(self::CONNECT_TIMEOUT)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9,id;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Connection' => 'keep-alive',
                    'Upgrade-Insecure-Requests' => '1',
                ])
                ->withCookies([
                    'euconsent' => 'COMPLETED',
                ], parse_url($url, PHP_URL_HOST))
                ->get($url);

            if (! $response->successful()) {
                Log::warning("Failed to fetch {$url}: HTTP {$response->status()}");

                return null;
            }

            $html = $response->body();

            if ($this->isBlockedPage($html)) {
                Log::warning("Likely blocked page detected for {$url}");

                return null;
            }

            return $html;
        } catch (\Exception $e) {
            Log::error("Exception fetching {$url}: {$e->getMessage()}");

            return null;
        }
    }

    public function parseArticle(string $html, string $sourceUrl = ''): array
    {
        $dom = $this->loadHtml($html);

        if (! $dom) {
            return [];
        }

        $result = [
            'title' => $this->extractTitle($dom),
            'content' => $this->extractContent($dom),
            'excerpt' => $this->extractExcerpt($dom),
            'author' => $this->extractAuthor($dom),
            'published_at' => $this->extractPublishedTime($dom),
            'image_url' => $this->extractMainImage($dom, $sourceUrl),
        ];

        return array_filter($result, fn ($v) => $v !== null && $v !== '');
    }

    private function loadHtml(string $html): ?\DOMDocument
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument;

        $dom->preserveWhiteSpace = false;

        if (defined('LIBXML_HTML_NOIMPLIED')) {
            $result = @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        } else {
            $result = @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        }

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        return $result ? $dom : null;
    }

    private function extractTitle(\DOMDocument $dom): ?string
    {
        foreach (self::DEFAULT_SELECTORS['title'] as $selector) {
            $node = $dom->querySelector($selector);
            if ($node) {
                $title = trim($node->textContent);

                if (mb_strlen($title) > 10 && mb_strlen($title) < 500) {
                    return $title;
                }
            }
        }

        $h1 = $dom->getElementsByTagName('h1')->item(0);

        return $h1 ? trim($h1->textContent) : null;
    }

    private function extractContent(\DOMDocument $dom): ?string
    {
        foreach (self::DEFAULT_SELECTORS['article'] as $selector) {
            $node = $dom->querySelector($selector);
            if ($node) {
                $html = $this->innerHtml($node);

                if (mb_strlen(strip_tags($html)) > 200) {
                    $cleaned = $this->cleanContent($html);

                    return $cleaned;
                }
            }
        }

        $articleTags = $dom->getElementsByTagName('article');
        if ($articleTags->length > 0) {
            foreach ($articleTags as $article) {
                $html = $this->innerHtml($article);
                if (mb_strlen(strip_tags($html)) > 200) {
                    return $this->cleanContent($html);
                }
            }
        }

        $main = $dom->getElementsByTagName('main');
        if ($main->length > 0) {
            $html = $this->innerHtml($main->item(0));

            if (mb_strlen(strip_tags($html)) > 200) {
                return $this->cleanContent($html);
            }
        }

        $body = $dom->getElementsByTagName('body');
        if ($body->length > 0) {
            $html = $this->innerHtml($body->item(0));

            if (mb_strlen(strip_tags($html)) > 200) {
                return $this->cleanContent($html);
            }
        }

        return null;
    }

    private function extractExcerpt(\DOMDocument $dom): ?string
    {
        $selectors = [
            '.entry-excerpt',
            '.article-excerpt',
            '.post-excerpt',
            '.excerpt',
            '[itemprop=description]',
            'meta[name=description]',
            'meta[property=og:description]',
        ];

        foreach ($selectors as $selector) {
            $node = $dom->querySelector($selector);
            if ($node) {
                if ($node instanceof \DOMElement && ($node->tagName === 'meta')) {
                    $content = $node->getAttribute('content');

                    if ($content) {
                        return $this->cleanText($content);
                    }
                }

                $text = trim($node->textContent);
                if ($text) {
                    return $this->cleanText($text);
                }
            }
        }

        return null;
    }

    private function extractAuthor(\DOMDocument $dom): ?string
    {
        foreach (self::DEFAULT_SELECTORS['author'] as $selector) {
            $node = $dom->querySelector($selector);
            if ($node) {
                $author = trim($node->textContent);
                if ($author && mb_strlen($author) < 100) {
                    return $this->cleanText($author);
                }
            }
        }

        return null;
    }

    private function extractPublishedTime(\DOMDocument $dom): ?string
    {
        foreach (self::DEFAULT_SELECTORS['published_time'] as $selector) {
            $node = $dom->querySelector($selector);
            if ($node) {
                if ($node instanceof \DOMElement && $node->hasAttribute('datetime')) {
                    return $node->getAttribute('datetime');
                }

                if ($node instanceof \DOMElement && $node->hasAttribute('content')) {
                    return $node->getAttribute('content');
                }

                $text = trim($node->textContent);
                if ($text) {
                    return $text;
                }
            }
        }

        $meta = $dom->querySelector('meta[property=article:published_time]');
        if ($meta) {
            return $meta->getAttribute('content');
        }

        return null;
    }

    private function extractMainImage(\DOMDocument $dom, string $sourceUrl): ?string
    {
        $ogImage = $dom->querySelector('meta[property=og:image]');
        if ($ogImage && $ogImage->getAttribute('content')) {
            return $this->makeAbsoluteUrl($ogImage->getAttribute('content'), $sourceUrl);
        }

        $twitterImage = $dom->querySelector('meta[name=twitter:image]');
        if ($twitterImage && $twitterImage->getAttribute('content')) {
            return $this->makeAbsoluteUrl($twitterImage->getAttribute('content'), $sourceUrl);
        }

        $firstImg = $dom->querySelector('article img, .post-content img, .entry-content img');
        if ($firstImg && $firstImg->getAttribute('src')) {
            return $this->makeAbsoluteUrl($firstImg->getAttribute('src'), $sourceUrl);
        }

        return null;
    }

    private function innerHtml(\DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return $html;
    }

    private function cleanContent(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<nav\b[^>]*>.*?<\/nav>/is', '', $html);
        $html = preg_replace('/<footer\b[^>]*>.*?<\/footer>/is', '', $html);
        $html = preg_replace('/<header\b[^>]*>.*?<\/header>/is', '', $html);
        $html = preg_replace('/<!--.*?-->/s', '', $html);
        $html = preg_replace('/\s+/', ' ', $html);

        $allowedTags = '<p><br><strong><em><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><figure><figcaption><img><table><thead><tbody><tr><th><td>';

        return strip_tags($html, $allowedTags);
    }

    private function cleanText(string $text): string
    {
        $text = preg_replace('/\s+/', ' ', $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($text);
    }

    private function makeAbsoluteUrl(string $url, string $baseUrl): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        if (str_starts_with($url, '/')) {
            $base = parse_url($baseUrl, PHP_URL_SCHEME).'://'.parse_url($baseUrl, PHP_URL_HOST);

            return $base.$url;
        }

        return $baseUrl.'/'.$url;
    }

    private function isBlockedPage(string $html): bool
    {
        $blockedIndicators = [
            'cf-error-details',
            'Access Denied',
            'Cloudflare',
            'Ray ID:',
            'attention required',
            'turned down',
            'DDoS protection',
            'checking your browser',
        ];

        foreach ($blockedIndicators as $indicator) {
            if (stripos($html, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }

    public function backfillAll(int $limit = 0, bool $force = false): array
    {
        $query = Article::whereRaw('LENGTH(COALESCE(content, excerpt, \'\')) < 200')
            ->orWhereNull('content');

        if (! $force) {
            $query->whereNull('synced_at')->orWhere('synced_at', '>', now()->subDay());
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $articles = $query->orderBy('published_at', 'desc')->get();

        $total = $articles->count();
        $success = 0;
        $failed = 0;

        foreach ($articles as $article) {
            $result = $this->fetchAndSave($article);

            if ($result) {
                $success++;
            } else {
                $failed++;
            }

            if (($success + $failed) % 10 === 0) {
                Log::info("Backfill progress: {$success} succeeded, {$failed} failed out of {$total}");
            }

            usleep(500000);
        }

        Log::info("Backfill complete: {$success} succeeded, {$failed} failed out of {$total} total");

        return [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
        ];
    }
}
