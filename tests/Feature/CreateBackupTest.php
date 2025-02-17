<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Events\BackupCreated;
use Itiden\Backup\Events\BackupFailed;
use Itiden\Backup\Exceptions\RestoreFailed;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Facades\Restorer;
use Itiden\Backup\Tests\SkippingPipe;

use function Itiden\Backup\Tests\user;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;
use function Statamic\trans;

describe('api:create', function (): void {
    it('cant create a backup by a guest', function (): void {
        $responseJson = postJson(cp_route('api.itiden.backup.store'));

        expect($responseJson->status())->toBe(401);
    });

    it('cant create a backup by a user without permissons a backup', function (): void {
        actingAs(user());

        $responseJson = postJson(cp_route('api.itiden.backup.store'));

        expect($responseJson->status())->toBe(403);
    });

    it('can create a backup by a user with create backups permission', function (): void {
        $user = user();

        $user
            ->assignRole('admin')
            ->save();

        actingAs($user);

        $responseJson = postJson(cp_route('api.itiden.backup.store'));

        $responseJson->assertExactJson(['message' => __('statamic-backup::backup.backup_started')]);

        expect(app(BackupRepository::class)
            ->all()
            ->count())->toBe(1);
    });

    it('can create backup from command', function (): void {
        expect(app(BackupRepository::class)
            ->all()
            ->count())->toBe(0);

        $this
            ->artisan('statamic:backup')
            ->assertExitCode(0);

        expect(app(BackupRepository::class)
            ->all()
            ->count())->toBe(1);
    });

    it('dispatches backup created event', function (): void {
        Event::fake();

        $user = user();

        $user
            ->assignRole('admin')
            ->save();

        actingAs($user);

        postJson(cp_route('api.itiden.backup.store'));

        Event::assertDispatched(BackupCreated::class, function (BackupCreated $event): bool {
            return (
                $event->backup->name ===
                app(BackupRepository::class)
                    ->all()
                    ->first()->name
            );
        });
    });

    it('dispatches failed event when error occurs', function (): void {
        Event::fake();

        // Set invalid pipeline to force an error
        config(['backup.pipeline' => ['backup' => 'not a valid pipeline']]);

        $user = user();

        $user
            ->assignRole('admin')
            ->save();

        actingAs($user);

        postJson(cp_route('api.itiden.backup.store'));

        Event::assertDispatched(BackupFailed::class);
    });

    it('sets created by metadata when user is authenticated', function (): void {
        $user = user();

        $user
            ->assignRole('admin')
            ->save();

        actingAs($user);

        postJson(cp_route('api.itiden.backup.store'));

        expect(app(BackupRepository::class)
            ->all()
            ->first()
            ->getMetadata()
            ->getCreatedBy())->toBe($user);
    });

    it('adds skipped pipes to meta', function (): void {
        $user = user();

        $user
            ->assignRole('admin')
            ->save();

        config()->set('backup.pipeline', [
            ...config('backup.pipeline'),
            SkippingPipe::class,
        ]);

        actingAs($user);

        postJson(cp_route('api.itiden.backup.store'));

        expect(app(BackupRepository::class)
            ->all()
            ->first()
            ->getMetadata()
            ->getSkippedPipes())->toHaveCount(1);
    });

    it('can encrypt backup with password', function (): void {
        config()->set('backup.password', 'password');

        $backup = Backuper::backup();

        config()->set('backup.password', null);

        expect(static fn() => Restorer::restore($backup))->toThrow(
            RestoreFailed::class,
            trans('statamic-backup::backup.restore.failed', ['name' => $backup->name]),
        );
    });
})->group('create backup');
