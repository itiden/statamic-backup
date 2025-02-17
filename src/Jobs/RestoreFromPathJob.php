<?php

declare(strict_types=1);

namespace Itiden\Backup\Jobs;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Restorer;
use Itiden\Backup\StateManager;

final class RestoreFromPathJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private string $path, private bool $deleteAfter)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(Restorer $restorer, Repository $cache): void
    {
        $restorer->restore(BackupDto::fromAbsolutePath($this->path));

        $cache->forget(StateManager::JOB_QUEUED_KEY);

        if ($this->deleteAfter) {
            File::delete($this->path);
        }
    }

    public function failed(): void
    {
        app(Repository::class)->forget(StateManager::JOB_QUEUED_KEY);
    }
}
