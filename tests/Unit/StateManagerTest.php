<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Enums\State;
use Itiden\Backup\StateManager;

use function Illuminate\Filesystem\join_paths;

describe('statemanager', function (): void {
    it('resolves the state correctly when there is no state file', function (): void {
        File::ensureDirectoryExists(config('backup.metadata_path'));
        File::delete(join_paths(config('backup.metadata_path'), StateManager::STATE_FILE));

        expect(app(StateManager::class)->getState())->toBe(State::Idle);
    });

    it('resolves to idle when the state file is empty', function (): void {
        File::ensureDirectoryExists(config('backup.metadata_path'));
        File::put(join_paths(config('backup.metadata_path'), StateManager::STATE_FILE), '');

        expect(app(StateManager::class)->getState())->toBe(State::Idle);
    });

    it('resolves to queued when a job has been put in the queue', function (): void {
        app(StateManager::class)->setState(State::BackupCompleted);

        Cache::put(StateManager::JOB_QUEUED_KEY, true);

        expect(app(StateManager::class)->getState())->toBe(State::Queued);

        Cache::forget(StateManager::JOB_QUEUED_KEY);

        expect(app(StateManager::class)->getState())->toBe(State::BackupCompleted);
    });
})->group('statemanager');
