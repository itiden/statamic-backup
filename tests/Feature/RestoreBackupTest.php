<?php

use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Event;
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
