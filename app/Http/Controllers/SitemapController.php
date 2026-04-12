<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\JobPosting;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function __invoke(): Sitemap
    {
        $sitemap = Sitemap::create(config('app.url'));

        // Static pages
        $sitemap->add(Url::create(route('news.index'))
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
            ->setPriority(1.0));

        $sitemap->add(Url::create(route('news.latest'))
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
            ->setPriority(0.9));

        $sitemap->add(Url::create(route('news.search'))
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.8));

        $sitemap->add(Url::create(route('jobs.index'))
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
            ->setPriority(0.9));

        $sitemap->add(Url::create(route('kalender-bali.index'))
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.7));

        // Articles
        Article::approved()
            ->published()
            ->whereNotNull('slug')
            ->cursor()
            ->each(function (Article $article) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('news.show', $article))
                        ->setLastModificationDate($article->published_at ?? $article->synced_at ?? now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority($article->is_featured ? 0.8 : 0.6)
                );
            });

        // Job postings
        JobPosting::approved()
            ->active()
            ->notExpired()
            ->whereNotNull('slug')
            ->cursor()
            ->each(function (JobPosting $job) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('jobs.show', $job))
                        ->setLastModificationDate($job->posted_date ?? now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.7)
                );
            });

        return $sitemap;
    }
}
