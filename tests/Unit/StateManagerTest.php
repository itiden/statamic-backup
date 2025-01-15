<?php

use Illuminate\Support\Facades\Storage;
use Itiden\Backup\State;
use Itiden\Backup\StateManager;

describe('statemanager', function () {
    it('resolves the state correctly when there is no state file', function () {
        Storage::build([
            'driver' => 'local',
            'root' => config('backup.metadata_path'),
        ])->delete('state');

        expect(app(StateManager::class)->getState())->toBe(State::Idle);
    });
})->group('statemanager');
