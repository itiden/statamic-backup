<?php

declare(strict_types=1);

namespace Itiden\Backup\Jobs;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Itiden\Backup\Restorer;
use Itiden\Backup\StateManager;
use Statamic\Contracts\Auth\User;

final class RestoreJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $id,
        private User $user,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(Restorer $backuper, Repository $cache): void
    {
        auth()->login($this->user); // ugly but it works;

        $backuper->restoreFromId($this->id);

        $cache->forget(StateManager::JOB_QUEUED_KEY);
    }

    public function failed(): void
    {
        app(Repository::class)->forget(StateManager::JOB_QUEUED_KEY);
    }
}
