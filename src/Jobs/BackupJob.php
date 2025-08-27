<?php

declare(strict_types=1);

namespace Itiden\Backup\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Itiden\Backup\Backuper;
use Itiden\Backup\StateManager;

final class BackupJob implements ShouldQueue
{
    // TODO(@neoisrecursive): Refactor this to use the `Illuminate\Foundation\Queue\Queueable` trait when dropping support for Laravel 10.
    use Queueable;
    use Dispatchable;
    use SerializesModels;
    use InteractsWithQueue;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Authenticatable $user,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(Backuper $backuper, Repository $cache): void
    {
        $backuper->backup(user: $this->user);

        $cache->forget(StateManager::JOB_QUEUED_KEY);
    }

    public function failed(): void
    {
        app(Repository::class)->forget(StateManager::JOB_QUEUED_KEY);
    }
}
