<?php

use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Events\BackupRestored;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses()->group('restore backup');

it('cant restore from path by a guest', function () {
    $response = postJson(cp_route('api.itiden.backup.restore', 'timestamp'));

    expect($response->status())->toBe(HttpResponse::HTTP_UNAUTHORIZED);
});

it('cant restore from path by a user without permissons a backup', function () {
    actingAs(user());

    $response = postJson(cp_route('api.itiden.backup.restore', 'timestamp'));

    expect($response->status())->toBe(HttpResponse::HTTP_FORBIDDEN);
});

it('can restore from path and delete after', function () {
    $backup = Backuper::backup();

    $user = user();

    $user->assignRole('super admin')->save();

    actingAs($user);

    $response = postJson(cp_route('api.itiden.backup.restore', $backup->timestamp));

    expect($response->status())->toBe(HttpResponse::HTTP_OK);
});

it('dispatches backup restored event', function () {
    Event::fake();
    $backup = Backuper::backup();

    $user = user();

    $user->assignRole('super admin')->save();

    actingAs($user);

    $response = postJson(cp_route('api.itiden.backup.restore', $backup->timestamp));

    Event::assertDispatched(BackupRestored::class, function ($event) use ($backup) {
        return $event->backup->timestamp === $backup->timestamp;
    });
    expect($response->status())->toBe(HttpResponse::HTTP_OK);
});

it('will not restore from command if you say no', function () {
    $backup = Backuper::backup();

    File::cleanDirectory(config('backup.content_path'));

    $this->artisan('statamic:backup:restore', ['path' => Storage::path($backup->path)])
        ->expectsConfirmation('Are you sure you want to restore your content?', 'no');

    expect(File::isEmptyDirectory(config('backup.content_path')))->toBeTrue();

    $this->artisan('statamic:backup:restore', ['path' => Storage::path($backup->path), '--force' => true])
        ->assertExitCode(0);
});

it('can restore from path command', function () {
    $backup = Backuper::backup();

    $this->artisan('statamic:backup:restore', ['path' => Storage::path($backup->path), '--force' => true])
        ->assertExitCode(0);

    expect(File::isEmptyDirectory(config('backup.content_path')))->toBeFalse();
});
