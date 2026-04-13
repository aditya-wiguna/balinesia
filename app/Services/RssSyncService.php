<?php

namespace App\Services;

use App\Models\Article;
use App\Models\NewsSource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RssSyncService
{
    private const FEED_BATCH_SIZE = 20;

    /**
     * Sync all active RSS-based news sources.
     */
    public function syncAllSources(): int
    {
        $total = 0;
        $sources = NewsSource::active()->get();

        foreach ($sources as $source) {
            $total += $this->syncSource($source);
        }

        return $total;
    }

    /**
     * Sync a single RSS source.
     */
    public function syncSource(NewsSource $source): int
    {
        $feedUrl = $source->config['feed_url'] ?? null;

        if (! $feedUrl) {
            Log::warning("RssSyncService: No feed_url in config for source {$source->name} [id={$source->id}]");

            return 0;
        }

        try {
            $response = Http::timeout(20)
                ->connectTimeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36',
                    'Accept' => 'application/rss+xml, application/xml, text/xml, */*',
                    'Accept-Language' => 'en-US,en;q=0.9,id;q=0.8',
                ])
                ->get($feedUrl);

            if ($response->status() === 403 || $response->status() === 401) {
                Log::warning("RssSyncService: Source {$source->name} blocked (HTTP {$response->status()}). Consider disabling or checking Cloudflare settings for: {$feedUrl}");

                return 0;
            }

            if (! $response->successful()) {
                Log::warning("RssSyncService: HTTP {$response->status()} for {$source->name} — {$feedUrl}");

                return 0;
            }

            $xml = @simplexml_load_string($response->body());

            if (! $xml) {
                Log::warning("RssSyncService: Could not parse XML from {$source->name} — {$feedUrl}");

                return 0;
            }

            $items = $this->extractItems($xml);

            if (empty($items)) {
                Log::info("RssSyncService: No items found in feed {$source->name} — {$feedUrl}");

                return 0;
            }

            return $this->processItems($source, $items);
        } catch (\Throwable $e) {
            Log::error("RssSyncService: Exception syncing {$source->name}: {$e->getMessage()}");

            return 0;
        }
    }

    /**
     * Extract channel metadata and items from an RSS/Atom XML object.
     */
    private function extractItems(\SimpleXMLElement $xml): array
    {
        $items = [];

        // RSS 2.0 — <channel><item>
        if ($xml->channel) {
            foreach ($xml->channel->item as $item) {
                $items[] = $this->parseRssItem($item);
            }
        }

        // Atom — <feed><entry>
        if ($xml->entry) {
            foreach ($xml->entry as $entry) {
                $items[] = $this->parseAtomEntry($entry);
            }
        }

        return $items;
    }

    /**
     * Parse a single RSS 2.0 <item>.
     */
    private function parseRssItem(\SimpleXMLElement $item): array
    {
        $title = $this->xmlString($item->title);
        $link = $this->xmlString($item->link);
        $description = $this->xmlString($item->description);
        $pubDate = $this->xmlString($item->pubDate);
        $author = $this->xmlString($item->author ?? $item->creator ?? null);
        $imageUrl = $this->extractImageFromDescription($description);
        $category = $this->xmlString($item->category ?? null);
        $guid = $this->xmlString($item->guid ?? $link);

        // Extract image from <enclosure> if present
        $enclosureUrl = $this->xmlAttribute($item->enclosure, 'url');
        if ($enclosureUrl && str_starts_with($this->xmlAttribute($item->enclosure, 'type'), 'image')) {
            $imageUrl = $enclosureUrl;
        }

        // Also try media:content or media:thumbnail
        if (! $imageUrl) {
            $imageUrl = $this->xmlAttribute($item->children('media', true)->content ?? null, 'url')
                ?? $this->xmlAttribute($item->children('media', true)->thumbnail ?? null, 'url')
                ?? null;
        }

        return [
            'title' => $title,
            'source_url' => $link,
            'description' => $description,
            'pub_date' => $this->parseDate($pubDate),
            'author' => $author,
            'image_url' => $imageUrl,
            'category' => $category,
            'guid' => $guid,
        ];
    }

    /**
     * Parse a single Atom <entry>.
     */
    private function parseAtomEntry(\SimpleXMLElement $entry): array
    {
        $title = $this->xmlString($entry->title);
        $link = $this->xmlAttribute(
            collect($entry->link)->first(fn ($l) => ($l['rel'] ?? 'alternate') === 'alternate'),
            'href'
        );
        $description = $this->xmlString($entry->summary ?? $entry->content ?? null);
        $pubDate = $this->xmlString($entry->published ?? $entry->updated ?? null);
        $author = $this->xmlString($entry->author?->name ?? null);
        $imageUrl = $this->extractImageFromDescription($description);
        $guid = $this->xmlString($entry->id ?? $link);

        return [
            'title' => $title,
            'source_url' => $link,
            'description' => $description,
            'pub_date' => $this->parseDate($pubDate),
            'author' => $author,
            'image_url' => $imageUrl,
            'category' => null,
            'guid' => $guid,
        ];
    }

    /**
     * Process items and save new articles to DB.
     */
    private function processItems(NewsSource $source, array $items): int
    {
        $synced = 0;
        $taken = 0;

        foreach ($items as $item) {
            if ($taken >= self::FEED_BATCH_SIZE) {
                break;
            }

            if (empty($item['source_url']) || empty($item['title'])) {
                continue;
            }

            // Skip if already exists by source_url
            if (Article::where('source_url', $item['source_url'])->exists()) {
                continue;
            }

            $slug = Article::generateUniqueSlug($item['title'], $source->id);
            $content = $this->cleanHtml($item['description'] ?? '');
            $excerpt = $this->makeExcerpt($item['description'] ?? '', 200);

            Article::create([
                'news_source_id' => $source->id,
                'external_id' => $item['guid'] ?? null,
                'title' => $item['title'],
                'slug' => $slug,
                'content' => $content,
                'excerpt' => $excerpt,
                'author' => $item['author'] ?? null,
                'image_url' => $item['image_url'] ?? null,
                'source_url' => $item['source_url'],
                'language' => $source->language,
                'published_at' => $item['pub_date'],
                'synced_at' => now(),
                'is_approved' => true,
            ]);

            $synced++;
            $taken++;
        }

        return $synced;
    }

    /**
     * Extract the first image URL from an HTML description string.
     */
    private function extractImageFromDescription(?string $html): ?string
    {
        if (! $html) {
            return null;
        }

        // Try <img src="..."> in description
        if (preg_match('/<img[^>]+src="([^"]+)"/i', $html, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Safely get string content from a SimpleXMLElement.
     */
    private function xmlString(mixed $el): ?string
    {
        if ($el === null) {
            return null;
        }

        $str = trim((string) $el);

        return $str === '' ? null : $str;
    }

    /**
     * Get an attribute value from a SimpleXMLElement by name.
     */
    private function xmlAttribute(mixed $el, string $attr): ?string
    {
        if (! $el) {
            return null;
        }

        $val = trim((string) ($el[$attr] ?? ''));

        return $val === '' ? null : $val;
    }

    /**
     * Parse an RSS/Atom date string into a Carbon instance or null.
     */
    private function parseDate(?string $dateStr): ?Carbon
    {
        if (! $dateStr) {
            return null;
        }

        try {
            return now()->parse($dateStr);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Strip HTML tags from description, truncate to roughly $length chars.
     */
    private function makeExcerpt(?string $html, int $length): ?string
    {
        if (! $html) {
            return null;
        }

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length).'…';
    }

    /**
     * Strip unwanted HTML, keeping only basic formatting tags.
     */
    private function cleanHtml(?string $html): ?string
    {
        if (! $html) {
            return null;
        }

        return strip_tags($html, '<p><br><strong><em><a><ul><ol><li>');
    }
}
