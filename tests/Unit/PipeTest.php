<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Pipes\Assets;
use Itiden\Backup\Pipes\ContentStachePipe;
use Itiden\Backup\Pipes\Users;
use Itiden\Backup\Support\Zipper;
use Statamic\Facades\Stache;

describe('pipes', function (): void {
    test('backup pipes can pass zipper instance', function (string $pipe): void {
        $temp_zip = config('backup.temp_path') . '/backup.zip';

        $zipper = Zipper::write($temp_zip);
        expect(app()
            ->make($pipe)
            ->backup($zipper, fn(Zipper $z): Zipper => $z))->toBeInstanceOf(Zipper::class);

        $zipper->close();

        File::delete($temp_zip);
    })->with([
        Users::class,
        ContentStachePipe::class,
        Assets::class,
    ]);

    test('restore pipes can pass closure', function (string $pipe): void {
        app(BackupRepository::class)->empty();
        $fixtues_path = __DIR__ . '/../__fixtures__';
        $fixtures_backup_path = Storage::path(config('backup.destination.path'));
        File::copyDirectory($fixtues_path, $fixtures_backup_path);

        $path = config('backup.temp_path') . '/backup';
        expect(app()
            ->make($pipe)
            ->restore($path, fn(string $z): string => $z))->toBe($path);

        File::deleteDirectory($fixtues_path);
        File::copyDirectory($fixtures_backup_path, $fixtues_path);
    })->with([
        Users::class,
        ContentStachePipe::class,
        Assets::class,
    ]);

    test('can skip a pipe with users', function (): void {
        $pipe = app()->make(Users::class);

        $callable = function (Zipper $z): Zipper {
            return $z;
        };

        File::deleteDirectory(Stache::store('users')->directory());

        $zipper = Zipper::write(config('backup.temp_path') . '/backup.zip');

        $pipe->backup(zip: $zipper, next: $callable);

        expect($zipper->getMeta())->toHaveKey(Users::class);
        expect($zipper->getMeta()[Users::class])->toHaveKey('skipped', 'No users found.');

        $zipper->close();
    });

    test('can skip a pipe with stache content', function (): void {
        $pipe = app()->make(ContentStachePipe::class);

        $callable = function (Zipper $z): Zipper {
            return $z;
        };

        config()->set('backup.stache_stores', ['non-existing-store']);

        $zipper = Zipper::write(config('backup.temp_path') . '/backup.zip');

        $pipe->backup(zip: $zipper, next: $callable);

        expect($zipper->getMeta())->toHaveKey(ContentStachePipe::class);
        expect($zipper->getMeta()[ContentStachePipe::class])->toHaveKey(
            'skipped',
            'No content paths found, is the Stache configured correctly?',
        );

        $zipper->close();
    });
})->group('pipes');
