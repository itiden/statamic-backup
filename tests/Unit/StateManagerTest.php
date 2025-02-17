<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Enums\State;
use Itiden\Backup\StateManager;

describe('statemanager', function (): void {
    it('resolves the state correctly when there is no state file', function (): void {
        Storage::build([
            'driver' => 'local',
            'root' => config('backup.metadata_path'),
        ])->delete(StateManager::STATE_FILE);

        expect(app(StateManager::class)->getState())->toBe(State::Idle);
    });

    it('resolves to idle when the state file is empty', function (): void {
        Storage::build([
            'driver' => 'local',
            'root' => config('backup.metadata_path'),
        ])->put('state', '');

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
