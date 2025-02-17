<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Enums\State;

final readonly class StateManager
{
    public const STATE_FILE = 'state';
    public const JOB_QUEUED_KEY = 'backup-job-queued';

    private Filesystem $filesystem;

    public function __construct(
        private Repository $cache,
    ) {
        $this->filesystem = Storage::build([
            'driver' => 'local',
            'root' => config('backup.metadata_path'),
        ]);
    }

    public function getState(): State
    {
        $state = State::tryFrom($this->filesystem->get(self::STATE_FILE) ?? '') ?? State::Idle;

        if (! in_array($state, [State::BackupInProgress, State::RestoreInProgress]) && $this->cache->has(self::JOB_QUEUED_KEY)) {
            $state = State::Queued;
        }

        return $state;
    }

    public function setState(State $state): void
    {
        $this->filesystem->put(self::STATE_FILE, $state->value);
    }
}
