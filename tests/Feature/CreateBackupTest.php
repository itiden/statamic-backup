<?php

use Itiden\Backup\Contracts\Repositories\BackupRepository;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

uses()->group('create backup');

beforeEach(function () {
    app(BackupRepository::class)->empty();
});

it('cant create a backup by a guest', function () {
    $responseJson = postJson(cp_route('api.itiden.backup.store'));
    $response = post(cp_route('api.itiden.backup.store'));

    expect($responseJson->status())->toBe(401);
    expect($response->status())->toBe(302);
});

it('cant create a backup by a user without permissons a backup', function () {
    actingAs(user());

    $responseJson = postJson(cp_route('api.itiden.backup.store'));
    $response = post(cp_route('api.itiden.backup.store'));

    expect($responseJson->status())->toBe(403);
    expect($response->status())->toBe(302);
});

it('can create a backup by a user with create backups permission', function () {
    $user = user();

    $user->assignRole('admin')->save();

    actingAs($user);

    $responseJson = postJson(cp_route('api.itiden.backup.store'));

    $backup = app(BackupRepository::class)->all()->first();

    expect($responseJson->status())->toBe(200);
    expect($responseJson->json('message'))->toBe('Backup created ' . $backup->name);
    expect(app(BackupRepository::class)->all()->count())->toBe(1);
});
