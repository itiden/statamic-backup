<?php

declare(strict_types=1);

use Itiden\Backup\Enums\State;
use Itiden\Backup\Http\Controllers\Api\StateController;
use Itiden\Backup\StateManager;

use function Itiden\Backup\Tests\user;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

covers(StateController::class);

describe('api:state', function (): void {
    beforeEach(fn() => app(StateManager::class)->setState(State::Idle));

    it('returns the correct state', function (): void {
        $user = user();

        $user->set('roles', ['admin'])->save();

        actingAs($user);

        getJson(cp_route('api.itiden.backup.state'))
            ->assertOk()
            ->assertExactJson([
                'state' => 'idle',
            ]);
    });
});
