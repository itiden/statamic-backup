<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Filesystem\Filesystem;
use Itiden\Backup\Enums\State;

use function Illuminate\Filesystem\join_paths;

final readonly class StateManager
{
    public const STATE_FILE = 'state';
    public const JOB_QUEUED_KEY = 'backup-job-queued';

    public function __construct(
        private Repository $cache,
        private Filesystem $filesystem,
    ) {
    }

    public function getState(): State
    {
        $path = join_paths(config('backup.metadata_path'), self::STATE_FILE);

        if (!$this->filesystem->exists($path)) {
            return State::Idle;
        }

        $state = State::tryFrom($this->filesystem->get($path)) ?? State::Idle;

        if (
            !in_array($state, [State::BackupInProgress, State::RestoreInProgress]) &&
                $this->cache->has(self::JOB_QUEUED_KEY)
        ) {
            $state = State::Queued;
        }

        return $state;
    }

    public function setState(State $state): void
    {
        $this->filesystem->ensureDirectoryExists(config('backup.metadata_path'));
        $this->filesystem->put(join_paths(config('backup.metadata_path'), self::STATE_FILE), $state->value, lock: true);
    }
}
