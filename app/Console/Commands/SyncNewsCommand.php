<?php

namespace App\Console\Commands;

use App\Services\RssSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('news:sync')]
#[Description('Sync news articles from all configured RSS sources')]
class SyncNewsCommand extends Command
{
    public function __construct(
        private readonly RssSyncService $rssSyncService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting news sync from RSS feeds...');

        $count = $this->rssSyncService->syncAllSources();

        $this->info("Successfully synced {$count} new articles.");

        return Command::SUCCESS;
    }
}
