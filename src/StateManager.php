<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Itiden\Backup\Enums\State;
use Itiden\Backup\Exceptions\ActionAlreadyInProgress;

use function Illuminate\Filesystem\join_paths;

final readonly class StateManager
{
    public const LOCK = 'backup';
    public const STATE_FILE = 'state';
    public const JOB_QUEUED_KEY = 'backup-job-queued';

    public function __construct(
        private Repository $cache,
        private Filesystem $filesystem,
    ) {}

    public function getState(): State
    {
        $path = join_paths(Config::string('backup.metadata_path'), self::STATE_FILE);

        if (!$this->filesystem->exists($path)) {
            return State::Idle;
        }

        $state = State::tryFrom($this->filesystem->get($path)) ?? State::Idle;

        if (
            !in_array($state, [State::BackupInProgress, State::RestoreInProgress], strict: true)
            && $this->cache->has(self::JOB_QUEUED_KEY)
        ) {
            $state = State::Queued;
        }

        return $state;
    }

    public function setState(State $state): void
    {
        $this->filesystem->ensureDirectoryExists(path: Config::string('backup.metadata_path'));
        $this->filesystem->put(
            path: join_paths(Config::string('backup.metadata_path'), self::STATE_FILE),
            contents: $state->value,
            lock: true,
        );
    }

    /**
     * Get the lock for the backup process.
     */
    public function getLock(): Lock
    {
        $lock = Cache::lock(name: StateManager::LOCK);
        $state = $this->getState();

        if (
            !$lock->get()
            || in_array(
                needle: $state,
                haystack: [State::BackupInProgress, State::RestoreInProgress],
                strict: true,
            )
        ) {
            throw ActionAlreadyInProgress::fromInvalidState($state);
        }

        return $lock;
    }

    public function dispatch(ShouldQueue $job): PendingDispatch
    {
        if ($this->cache->has(self::JOB_QUEUED_KEY)) {
            throw ActionAlreadyInProgress::fromInQueue();
        }

        $this->cache->put(self::JOB_QUEUED_KEY, true);
        return dispatch($job);
    }
}
