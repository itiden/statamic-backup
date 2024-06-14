<?php

use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Facades\Backuper;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;

uses()->group('delete backup');

it('cant be deleted by a guest', function () {
    $backup = Backuper::backup();

    $res = deleteJson(cp_route('api.itiden.backup.destroy', $backup->timestamp));

    expect($res->status())->toBe(401);
    expect(app(BackupRepository::class)->all())->toHaveCount(1);
});

it('cant be deleted by a user without delete permisson', function () {
    $backup = Backuper::backup();

    actingAs(user());

    $res = deleteJson(cp_route('api.itiden.backup.destroy', $backup->timestamp));

    expect($res->status())->toBe(403);
    expect(app(BackupRepository::class)->all())->toHaveCount(1);
});

it('can be deleted by a user with delete backups permission', function () {
    $backup = Backuper::backup();

    $user = user();

    $user->assignRole('super admin')->save();

    actingAs($user);

    $response = deleteJson(cp_route('api.itiden.backup.destroy', $backup->timestamp));

    expect($response->status())->toBe(200);
    expect($response->json('message'))->toBe(__('statamic-backup::backup.destroy.success', ['name' => $backup->name]));

    expect(app(BackupRepository::class)->all())->toHaveCount(0);
});
