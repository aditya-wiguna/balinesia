<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\ArticleContentFetcher;
use Illuminate\Console\Command;

class BackfillArticleContent extends Command
{
    protected $signature = 'app:backfill-article-content
        {--limit=0 : Maximum number of articles to process (0 = unlimited)}
        {--force : Re-fetch articles even if recently synced}
        {--dry-run : Show which articles would be processed without fetching}
    ';

    protected $description = 'Scrape full content from source URLs for articles with partial content';

    public function handle(ArticleContentFetcher $fetcher): int
    {
        $limit = (int) $this->option('limit');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        $query = Article::query()
            ->where(function ($q) {
                $q->whereRaw('LENGTH(COALESCE(content, excerpt, \'\')) < 200')
                    ->orWhereNull('content');
            })
            ->whereNull('content_fetched_at');

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

        if ($total === 0) {
            $this->info('No articles need content backfill.');

            return Command::SUCCESS;
        }

        $this->info("Found {$total} article(s) needing content backfill.");

        if ($dryRun) {
            $this->table(
                ['ID', 'Title', 'Source URL', 'Current Content Length'],
                $articles->map(fn ($a) => [
                    $a->id,
                    mb_substr($a->title, 0, 50).(mb_strlen($a->title) > 50 ? '...' : ''),
                    $a->source_url,
                    mb_strlen($a->content ?? $a->excerpt ?? ''),
                ])
            );

            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($articles as $article) {
            $result = $fetcher->fetchAndSave($article);

            if ($result) {
                $success++;
            } else {
                $failed++;
            }

            $bar->advance();
            usleep(300000);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Backfill complete: {$success} succeeded, {$failed} failed out of {$total} total");

        if ($failed > 0) {
            $this->warn('Some articles failed. Check logs for details.');
        }

        return Command::SUCCESS;
    }
}
