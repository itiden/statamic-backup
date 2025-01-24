<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Events\BackupRestored;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('api:restore', function (): void {
    it('cant restore by timestamp by a guest', function (): void {
        $response = postJson(cp_route('api.itiden.backup.restore', 'timestamp'));

        expect($response->status())->toBe(Response::HTTP_UNAUTHORIZED);
    });

    it('cant restore by timestamp by a user without permissons a backup', function (): void {
        actingAs(user());

        $response = postJson(cp_route('api.itiden.backup.restore', 'timestamp'));

        expect($response->status())->toBe(Response::HTTP_FORBIDDEN);
    });

    it('returns error if backup is not found', function (): void {
        $user = user();

        $user
            ->assignRole('super admin')
            ->save();

        actingAs($user);

        $response = postJson(cp_route('api.itiden.backup.restore', 'timestamp'));

        expect($response->status())->toBe(Response::HTTP_INTERNAL_SERVER_ERROR);
    });

    it('can restore by timestamp', function (): void {
        $backup = Backuper::backup();

        $user = user();

        $user
            ->assignRole('super admin')
            ->save();

        actingAs($user);

        $response = postJson(cp_route('api.itiden.backup.restore', $backup->timestamp));

        expect($response->status())->toBe(Response::HTTP_OK);
    });

    it('dispatches backup restored event', function (): void {
        Event::fake();
        $backup = Backuper::backup();

        $user = user();

        $user
            ->assignRole('super admin')
            ->save();

        actingAs($user);

        $response = postJson(cp_route('api.itiden.backup.restore', $backup->timestamp));

        Event::assertDispatched(BackupRestored::class, function ($event) use ($backup) {
            return $event->backup->timestamp === $backup->timestamp;
        });
        expect($response->status())->toBe(Response::HTTP_OK);
    });

    it('will not restore from command if you say no', function (): void {
        $backup = Backuper::backup();

        File::cleanDirectory(config('backup.content_path'));

        $this
            ->artisan('statamic:backup:restore', ['--path' => Storage::path($backup->path)])
            ->expectsConfirmation('Are you sure you want to restore your content?', 'no');

        expect(File::isEmptyDirectory(config('backup.content_path')))->toBeTrue();

        $this
            ->artisan('statamic:backup:restore', ['--path' => Storage::path($backup->path), '--force' => true])
            ->assertExitCode(0);
    });

    it('can restore from path command', function (): void {
        $backup = Backuper::backup();

        $this
            ->artisan('statamic:backup:restore', ['--path' => Storage::path($backup->path), '--force' => true])
            ->assertExitCode(0);

        expect(File::isEmptyDirectory(config('backup.content_path')))->toBeFalse();
    });

    it('will add an restore entry to metadata', function (): void {
        $backup = Backuper::backup();

        $user = user();

        $user
            ->assignRole('super admin')
            ->save();

        actingAs($user);

        $response = postJson(cp_route('api.itiden.backup.restore', $backup->timestamp));

        expect($response->status())->toBe(Response::HTTP_OK);
        expect($backup
            ->getMetadata()
            ->getRestores())->toHaveCount(1);
        expect(
            $backup
                ->getMetadata()
                ->getRestores()[0]->userId,
        )->toBe($user->id);
    });
})->group('restore backup');
