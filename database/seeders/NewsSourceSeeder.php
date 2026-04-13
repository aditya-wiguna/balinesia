<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\NewsSource;
use Illuminate\Database\Seeder;

class NewsSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            // Indonesian-language feeds
            [
                'name' => 'ANTARA News Bali',
                'url' => 'https://bali.antaranews.com',
                'language' => 'id',
                'is_active' => true,
                'config' => ['feed_url' => 'https://bali.antaranews.com/rss/terkini.xml'],
            ],
            [
                'name' => 'ANTARA News Bali Top',
                'url' => 'https://bali.antaranews.com',
                'language' => 'id',
                'is_active' => true,
                'config' => ['feed_url' => 'https://bali.antaranews.com/rss/top-news.xml'],
            ],
            [
                'name' => 'ANTARA News Bali Nasional',
                'url' => 'https://bali.antaranews.com',
                'language' => 'id',
                'is_active' => true,
                'config' => ['feed_url' => 'https://bali.antaranews.com/rss/nasional.xml'],
            ],
            [
                'name' => 'ANTARA News Bali Ekonomi',
                'url' => 'https://bali.antaranews.com',
                'language' => 'id',
                'is_active' => true,
                'config' => ['feed_url' => 'https://bali.antaranews.com/rss/ekonomi.xml'],
            ],
            [
                'name' => 'ANTARA News Bali Pariwisata',
                'url' => 'https://bali.antaranews.com',
                'language' => 'id',
                'is_active' => true,
                'config' => ['feed_url' => 'https://bali.antaranews.com/rss/pariwisata.xml'],
            ],
            [
                'name' => 'ANTARA News Bali Olahraga',
                'url' => 'https://bali.antaranews.com',
                'language' => 'id',
                'is_active' => true,
                'config' => ['feed_url' => 'https://bali.antaranews.com/rss/olahraga.xml'],
            ],
            [
                'name' => 'Bali Satu Berita',
                'url' => 'https://balisatuberita.com',
                'language' => 'id',
                'is_active' => true,
                'config' => ['feed_url' => 'https://balisatuberita.com/rss/latest-posts'],
            ],
            // English-language feeds
            [
                'name' => 'The Bali Sun',
                'url' => 'https://thebalisun.com',
                'language' => 'en',
                'is_active' => true,
                'config' => ['feed_url' => 'https://thebalisun.com/feed'],
            ],
            [
                'name' => 'NOW! Bali',
                'url' => 'https://nowbali.co.id',
                'language' => 'en',
                'is_active' => true,
                'config' => ['feed_url' => 'https://nowbali.co.id/feed'],
            ],
            [
                'name' => 'The Beat Bali',
                'url' => 'https://thebeatbali.com',
                'language' => 'en',
                'is_active' => true,
                'config' => ['feed_url' => 'https://thebeatbali.com/feed'],
            ],
            [
                'name' => 'Budaya Bali',
                'url' => 'https://budayabali.com',
                'language' => 'id',
                'is_active' => true,
                'config' => ['feed_url' => 'https://budayabali.com/rss/latest-posts'],
            ],
        ];

        foreach ($sources as $source) {
            NewsSource::updateOrCreate(
                ['url' => $source['url'], 'name' => $source['name']],
                $source
            );
        }

        // Disable the old balinews.id WordPress source (blocked by Cloudflare)
        NewsSource::where('url', 'https://balinews.id')->update(['is_active' => false]);

        $categories = [
            ['name' => 'Culture', 'slug' => 'culture', 'color' => '#C9A227', 'icon' => 'heroicon-o-sparkles'],
            ['name' => 'Tourism', 'slug' => 'tourism', 'color' => '#0A9396', 'icon' => 'heroicon-o-map'],
            ['name' => 'Business', 'slug' => 'business', 'color' => '#2D6A4F', 'icon' => 'heroicon-o-briefcase'],
            ['name' => 'Events', 'slug' => 'events', 'color' => '#E85D04', 'icon' => 'heroicon-o-calendar'],
            ['name' => 'Nature', 'slug' => 'nature', 'color' => '#94D2BD', 'icon' => 'heroicon-o-sun'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
