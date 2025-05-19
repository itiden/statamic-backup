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
use Itiden\Backup\Restorer;
use Itiden\Backup\StateManager;

final class RestoreJob implements ShouldQueue
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
        private string $id,
        private Authenticatable $user,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(Restorer $backuper, Repository $cache): void
    {
        $backuper->restoreFromId(
            id: $this->id,
            user: $this->user,
        );

        $cache->forget(StateManager::JOB_QUEUED_KEY);
    }

    public function failed(): void
    {
        app(Repository::class)->forget(StateManager::JOB_QUEUED_KEY);
    }
}
