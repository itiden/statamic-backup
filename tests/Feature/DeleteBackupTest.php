<?php

declare(strict_types=1);

use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Facades\Backuper;

use function Itiden\Backup\Tests\user;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;

describe('api:destroy', function (): void {
    it('cant be deleted by a guest', function (): void {
        $backup = Backuper::backup();

        $res = deleteJson(cp_route('api.itiden.backup.destroy', $backup->id));

        expect($res->status())->toBe(401);
        expect(app(BackupRepository::class)->all())->toHaveCount(1);
    });

    it('cant be deleted by a user without delete permisson', function (): void {
        $backup = Backuper::backup();

        actingAs(user());

        $res = deleteJson(cp_route('api.itiden.backup.destroy', $backup->id));

        expect($res->status())->toBe(403);
        expect(app(BackupRepository::class)->all())->toHaveCount(1);
    });

    it('can be deleted by a user with delete backups permission', function (): void {
        $backup = Backuper::backup();

        $user = user();

        $user
            ->assignRole('super admin')
            ->save();

        actingAs($user);

        $response = deleteJson(cp_route('api.itiden.backup.destroy', $backup->id));

        expect($response->status())->toBe(200);
        expect($response->json('message'))->toBe('Deleted ' . $backup->name);

        expect(app(BackupRepository::class)->all())->toHaveCount(0);
    });
})->group('delete backup');
