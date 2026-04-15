<?php

namespace App\Services;

use App\Models\Article;
use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArticleContentFetcher
{
    private const TIMEOUT_SECONDS = 30;

    private const CONNECT_TIMEOUT = 10;

    private const CACHE_TTL_SECONDS = 86400;

    private const CONTENT_SELECTORS = [
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
    ];

    private const TITLE_SELECTORS = [
        'h1.entry-title',
        'h1.post-title',
        'h1.article-title',
        'h1[itemprop=headline]',
        '.entry-title',
        '.post-title',
        'article h1',
        'h1',
    ];

    private const AUTHOR_SELECTORS = [
        'span[itemprop=author]',
        '.author-name',
        '.byline',
        '.post-author',
        '.article-author',
        '[rel=author]',
    ];

    private const PUBLISHED_SELECTORS = [
        'time[itemprop=datePublished]',
        'time.entry-date',
        '.post-date',
        '.published',
    ];

    private const EXCERPT_SELECTORS = [
        '.entry-excerpt',
        '.article-excerpt',
        '.post-excerpt',
        '.excerpt',
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

    private function loadHtml(string $html): ?DOMDocument
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = false;

        $encoded = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        if (defined('LIBXML_HTML_NOIMPLIED')) {
            @$dom->loadHTML($encoded, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        } else {
            @$dom->loadHTML($encoded);
        }

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        return $dom;
    }

    private function xpath(DOMDocument $dom, string $expression): DOMNodeList|false
    {
        $xpath = new DOMXPath($dom);

        return $xpath->query($expression);
    }

    private function cssToXPath(string $css): string
    {
        $css = trim($css);

        $xpath = '';
        $remaining = $css;

        while (strlen($remaining) > 0) {
            if (preg_match('/^([a-zA-Z][a-zA-Z0-9]*)?(\[[^\]]+\])?/i', $remaining, $m)) {
                $tag = $m[1] ?? '*';

                if (isset($m[2]) && preg_match_all('/\[([^\]]+)\]/', $m[2], $attrMatches)) {
                    foreach ($attrMatches[1] as $attr) {
                        if (preg_match('/^([a-zA-Z-]+)([~|^$*]?=)?"?([^"]*)"?$/', $attr, $am)) {
                            $attrName = $am[1];
                            $op = $am[2] ?? '=';
                            $val = $am[3] ?? '';

                            $xpath .= "[@{$attrName}{$op}'{$val}']";
                        } else {
                            $xpath .= "[@{$attr}]";
                        }
                    }
                }

                $xpath .= "//{$tag}";
                $remaining = substr($remaining, strlen($m[0]));
            } else {
                break;
            }
        }

        if ($xpath === '' || $xpath[0] !== '/') {
            $xpath = '//'.ltrim($xpath, '/');
        }

        return $xpath;
    }

    private function extractTitle(DOMDocument $dom): ?string
    {
        foreach (self::TITLE_SELECTORS as $selector) {
            $xpathExpr = $this->cssToXPath($selector);
            $nodes = $this->xpath($dom, $xpathExpr);

            if ($nodes && $nodes->length > 0) {
                $title = trim($nodes->item(0)?->textContent ?? '');

                if (mb_strlen($title) > 10 && mb_strlen($title) < 500) {
                    return $title;
                }
            }
        }

        $h1List = $dom->getElementsByTagName('h1');
        if ($h1List->length > 0) {
            return trim($h1List->item(0)?->textContent ?? '');
        }

        return null;
    }

    private function extractContent(DOMDocument $dom): ?string
    {
        foreach (self::CONTENT_SELECTORS as $selector) {
            $xpathExpr = $this->cssToXPath($selector);
            $nodes = $this->xpath($dom, $xpathExpr);

            if ($nodes && $nodes->length > 0) {
                foreach ($nodes as $node) {
                    $html = $this->innerHtml($node);
                    $textLen = mb_strlen(strip_tags($html));

                    if ($textLen > 200) {
                        return $this->cleanContent($html);
                    }
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

    private function extractExcerpt(DOMDocument $dom): ?string
    {
        foreach (self::EXCERPT_SELECTORS as $selector) {
            $xpathExpr = $this->cssToXPath($selector);
            $nodes = $this->xpath($dom, $xpathExpr);

            if ($nodes && $nodes->length > 0) {
                $text = trim($nodes->item(0)?->textContent ?? '');
                if ($text) {
                    return $this->cleanText($text);
                }
            }
        }

        $metaDesc = $this->xpath($dom, "//meta[@name='description']/@content");
        if ($metaDesc && $metaDesc->length > 0) {
            return $this->cleanText($metaDesc->item(0)?->nodeValue ?? '');
        }

        $ogDesc = $this->xpath($dom, "//meta[@property='og:description']/@content");
        if ($ogDesc && $ogDesc->length > 0) {
            return $this->cleanText($ogDesc->item(0)?->nodeValue ?? '');
        }

        $itemPropDesc = $this->xpath($dom, "//*[@itemprop='description']");
        if ($itemPropDesc && $itemPropDesc->length > 0) {
            return $this->cleanText($itemPropDesc->item(0)?->textContent ?? '');
        }

        return null;
    }

    private function extractAuthor(DOMDocument $dom): ?string
    {
        foreach (self::AUTHOR_SELECTORS as $selector) {
            $xpathExpr = $this->cssToXPath($selector);
            $nodes = $this->xpath($dom, $xpathExpr);

            if ($nodes && $nodes->length > 0) {
                $author = trim($nodes->item(0)?->textContent ?? '');
                if ($author && mb_strlen($author) < 100) {
                    return $this->cleanText($author);
                }
            }
        }

        return null;
    }

    private function extractPublishedTime(DOMDocument $dom): ?string
    {
        foreach (self::PUBLISHED_SELECTORS as $selector) {
            $xpathExpr = $this->cssToXPath($selector);
            $nodes = $this->xpath($dom, $xpathExpr);

            if ($nodes && $nodes->length > 0) {
                $node = $nodes->item(0);
                if ($node instanceof DOMNode) {
                    $datetime = $node->attributes?->getNamedItem('datetime')?->nodeValue;
                    if ($datetime) {
                        return $datetime;
                    }

                    $content = $node->attributes?->getNamedItem('content')?->nodeValue;
                    if ($content) {
                        return $content;
                    }

                    $text = trim($node->textContent ?? '');
                    if ($text) {
                        return $text;
                    }
                }
            }
        }

        $metaTime = $this->xpath($dom, "//meta[@property='article:published_time']/@content");
        if ($metaTime && $metaTime->length > 0) {
            return $metaTime->item(0)?->nodeValue;
        }

        return null;
    }

    private function extractMainImage(DOMDocument $dom, string $sourceUrl): ?string
    {
        $ogImage = $this->xpath($dom, "//meta[@property='og:image']/@content");
        if ($ogImage && $ogImage->length > 0) {
            $url = $ogImage->item(0)?->nodeValue;
            if ($url) {
                return $this->makeAbsoluteUrl($url, $sourceUrl);
            }
        }

        $twitterImage = $this->xpath($dom, "//meta[@name='twitter:image']/@content");
        if ($twitterImage && $twitterImage->length > 0) {
            $url = $twitterImage->item(0)?->nodeValue;
            if ($url) {
                return $this->makeAbsoluteUrl($url, $sourceUrl);
            }
        }

        $firstImg = $this->xpath($dom, "//article//img | //*[@class='post-content']//img | //*[@class='entry-content']//img");
        if ($firstImg && $firstImg->length > 0) {
            $src = $firstImg->item(0)?->attributes?->getNamedItem('src')?->nodeValue;
            if ($src) {
                return $this->makeAbsoluteUrl($src, $sourceUrl);
            }
        }

        return null;
    }

    private function innerHtml(DOMNode $node): string
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
            $query->where(function ($q) {
                $q->whereNull('synced_at')
                    ->orWhere('synced_at', '>', now()->subDay());
            });
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
