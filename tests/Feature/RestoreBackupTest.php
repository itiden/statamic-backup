<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Enums\State;
use Itiden\Backup\Events\BackupRestored;
use Itiden\Backup\Events\RestoreFailed;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\StateManager;

use function Itiden\Backup\Tests\fixtures_path;
use function Itiden\Backup\Tests\user;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('api:restore', function (): void {
    it('cant restore by id by a guest', function (): void {
        $response = postJson(cp_route('api.itiden.backup.restore', 'id'));

        expect($response->status())->toBe(Response::HTTP_UNAUTHORIZED);
    });

    it('cant restore by id by a user without permissons a backup', function (): void {
        actingAs(user());

        $response = postJson(cp_route('api.itiden.backup.restore', 'id'));

        expect($response->status())->toBe(Response::HTTP_FORBIDDEN);
    });

    it('returns error if backup is not found', function (): void {
        $user = user();

        $user->assignRole('super admin')->save();

        actingAs($user);

        $response = postJson(cp_route('api.itiden.backup.restore', 'id'));

        expect($response->status())->toBe(Response::HTTP_INTERNAL_SERVER_ERROR);
    });

    it('sets correct states if restore fails', function (): void {
        Event::fake();
        $backup = Backuper::backup();

        $user = user();

        $user->assignRole('super admin')->save();

        actingAs($user);

        config()->set('backup.pipeline', ['im-not-valid']);
        postJson(cp_route('api.itiden.backup.restore', $backup->id));

        Event::assertDispatched(RestoreFailed::class);
        expect(Cache::has(StateManager::JOB_QUEUED_KEY))->toBeFalse();
        expect(app(StateManager::class)->getState())->toBe(State::RestoreFailed);
    });

    it('can restore by id', function (): void {
        $backup = Backuper::backup();

        $user = user();

        $user->assignRole('super admin')->save();

        actingAs($user);

        $response = postJson(cp_route('api.itiden.backup.restore', $backup->id));

        expect($response->status())->toBe(Response::HTTP_OK);
    });

    it('dispatches backup restored event', function (): void {
        Event::fake();
        $backup = Backuper::backup();

        $user = user();

        $user->assignRole('super admin')->save();

        actingAs($user);

        $response = postJson(cp_route('api.itiden.backup.restore', $backup->id));

        Event::assertDispatched(BackupRestored::class, function (BackupRestored $event) use ($backup): bool {
            return $event->backup->id === $backup->id;
        });
        expect($response->status())->toBe(Response::HTTP_OK);
    });

    it('will not restore from command if you say no', function (): void {
        $backup = Backuper::backup();

        File::cleanDirectory(fixtures_path('content/collections'));

        $this->artisan('statamic:backup:restore', ['--path' => Storage::path($backup->path)])->expectsConfirmation(
            'Are you sure you want to restore your content?',
            'no',
        );

        expect(File::isEmptyDirectory(fixtures_path('content/collections')))->toBeTrue();

        $this->artisan('statamic:backup:restore', [
            '--path' => Storage::path($backup->path),
            '--force' => true,
        ])->assertExitCode(0);
    });

    it('can restore from path command', function (): void {
        $backup = Backuper::backup();

        $this->artisan('statamic:backup:restore', [
            '--path' => Storage::path($backup->path),
            '--force' => true,
        ])->assertExitCode(0);

        expect(File::isEmptyDirectory(fixtures_path('content')))->toBeFalse();
    });

    it('will add an restore entry to metadata', function (): void {
        $backup = Backuper::backup();

        $user = user();

        $user->assignRole('super admin')->save();

        actingAs($user);

        $response = postJson(cp_route('api.itiden.backup.restore', $backup->id));

        expect($response->status())->toBe(Response::HTTP_OK);
        expect($backup->getMetadata()->getRestores())->toHaveCount(1);
        expect($backup->getMetadata()->getRestores()[0]->userId)->toBe($user->id);
    });
})->group('restore backup');
