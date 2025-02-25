<?php

declare(strict_types=1);

use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Pipes\Users;

use function Itiden\Backup\Tests\user;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

describe('api:view', function (): void {
    test('guest cant view backups', function (): void {
        $this
            ->get(cp_route('itiden.backup.index'))
            ->assertRedirect(cp_route('login'));
    });

    test('user without permission cant view backups', function (): void {
        $this->withoutVite();

        actingAs(user());

        get(cp_route('itiden.backup.index'))->assertRedirect();
    });

    test('user with permission can view backups', function (): void {
        $this->withoutVite();

        $user = user();

        $user
            ->set('roles', ['admin'])
            ->save();

        actingAs($user);

        get(cp_route('itiden.backup.index'))
            ->assertOk()
            ->assertViewIs('itiden-backup::backups');
    });

    test('user without permission cant get backups from api', function (): void {
        $this->withoutVite();

        $user = user();

        actingAs($user);

        getJson(cp_route('api.itiden.backup.index'))->assertForbidden();
    });

    test('user with permission can get backups from api', function (): void {
        $this->withoutVite();
        $this->withoutExceptionHandling();

        $user = user();

        $user
            ->set('roles', ['admin'])
            ->save();

        actingAs($user);

        $backup = Backuper::backup();

        // Set some metadata so we can test it has correct structure
        $backup
            ->getMetadata()
            ->addDownload($user);
        $backup
            ->getMetadata()
            ->addRestore($user);
        $backup
            ->getMetadata()
            ->addSkippedPipe(Users::class, 'Oh no it failed!');

        getJson(cp_route('api.itiden.backup.index'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['*' => [
                    'name',
                    'size',
                    'path',
                    'created_at',
                    'timestamp',
                    'metadata' => [
                        'created_by',
                        'downloads',
                        'restores',
                        'skipped_pipes',
                    ],
                ]],
                'meta' => ['columns' => ['*' => [
                    'label',
                    'field',
                    'visible',
                ]]],
            ]);
    });
})->group('view');
