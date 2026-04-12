<?php

namespace App\Console\Commands;

use App\Services\JobSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('jobs:sync')]
#[Description('Sync job postings from LokerBali')]
class SyncJobsCommand extends Command
{
    public function __construct(
        private readonly JobSyncService $syncService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting job sync from LokerBali.id and LokerBali.info...');

        $count = $this->syncService->syncAll();

        $this->info("Successfully synced {$count} new job postings.");

        return Command::SUCCESS;
    }
}
