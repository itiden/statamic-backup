<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Itiden\Backup\Jobs\BackupJob;
use Itiden\Backup\Jobs\RestoreFromPathJob;
use Itiden\Backup\Jobs\RestoreFromTimestampJob;
use Statamic\Contracts\Auth\User;

use function Itiden\Backup\Tests\user;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('prevents:simultaneous-actions', function (): void {
    it('prevents simultaneous backup jobs', function (): void {
        Bus::fake();

        $user = tap(user())
            ->assignRole('admin')
            ->save();

        actingAs($user);

        postJson(cp_route('api.itiden.backup.store'));

        postJson(cp_route('api.itiden.backup.store'))->assertServerError();

        Bus::assertDispatchedTimes(BackupJob::class, 1);
    });

    it('prevents simultaneous restore jobs', function (): void {
        Bus::fake();

        $user = tap(user())
            ->assignRole('super admin')
            ->save();

        actingAs($user);

        postJson(cp_route('api.itiden.backup.restore', [
            'timestamp' => now()->timestamp,
        ]));

        postJson(cp_route('api.itiden.backup.restore', [
            'timestamp' => now()->timestamp,
        ]))->assertServerError();

        Bus::assertDispatchedTimes(RestoreFromTimestampJob::class, 1);
    });

    it('prevents simultaneous restore from path jobs', function (): void {
        Bus::fake();

        $user = tap(user())
            ->assignRole('super admin')
            ->save();

        actingAs($user);

        postJson(cp_route('api.itiden.backup.restore-from-path'), [
            'path' => 'test',
        ]);

        postJson(cp_route('api.itiden.backup.restore-from-path'), [
            'path' => 'test',
        ])->assertServerError();

        Bus::assertDispatchedTimes(RestoreFromPathJob::class, 1);
    });

    it('prevents other actions when something is queued', function (): void {
        Bus::fake();

        $user = tap(user(), fn(User $user) => $user
            ->assignRole('super admin')
            ->save());

        actingAs($user);

        postJson(cp_route('api.itiden.backup.restore', [
            'timestamp' => now()->timestamp,
        ]));

        postJson(cp_route('api.itiden.backup.store'))->assertServerError();

        postJson(cp_route('api.itiden.backup.restore-from-path'), [
            'path' => 'test',
        ])->assertServerError();

        postJson(cp_route('api.itiden.backup.restore', [
            'timestamp' => now()->timestamp,
        ]))->assertServerError();

        Bus::assertNotDispatched(BackupJob::class);
        Bus::assertNotDispatched(RestoreFromPathJob::class);
        Bus::assertDispatchedTimes(RestoreFromTimestampJob::class, 1);
    });
});
