<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

final readonly class StateManager
{
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = Storage::build([
            'driver' => 'local',
            'root' => config('backup.metadata_path'),
        ]);
    }

    public function getState(): State
    {
        if (!$this->filesystem->exists('state')) {
            return State::Idle;
        }

        return State::from($this->filesystem->get('state'));
    }

    public function setState(State $state): void
    {
        $this->filesystem->put('state', $state->value);
    }
}
