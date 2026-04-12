<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\JobPosting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('app:generate-slugs')]
#[Description('Generate unique slugs for existing articles and job postings')]
class GenerateSlugsCommand extends Command
{
    public function handle(): int
    {
        $this->info('Generating slugs for articles...');
        $articles = Article::whereNull('slug')->get();
        $count = 0;

        foreach ($articles as $article) {
            $base = Str::slug($article->title, '-');
            $slug = $base;
            $counter = 1;

            while (Article::where('news_source_id', $article->news_source_id)->where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
                $slug = $base.'-'.$counter;
                $counter++;
            }

            $article->slug = $slug;
            $article->saveQuietly();
            $count++;
        }

        $this->info("Generated {$count} slugs for articles.");

        $this->info('Generating slugs for job postings...');
        $jobs = JobPosting::whereNull('slug')->get();
        $jobCount = 0;

        foreach ($jobs as $job) {
            $base = Str::slug($job->job_title, '-');
            $slug = $base;
            $counter = 1;

            while (JobPosting::where('slug', $slug)->where('id', '!=', $job->id)->exists()) {
                $slug = $base.'-'.$counter;
                $counter++;
            }

            $job->slug = $slug;
            $job->saveQuietly();
            $jobCount++;
        }

        $this->info("Generated {$jobCount} slugs for job postings.");

        return Command::SUCCESS;
    }
}
