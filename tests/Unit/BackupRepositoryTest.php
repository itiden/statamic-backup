<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Events\BackupDeleted;
use Itiden\Backup\Facades\Backuper;

describe('repository:backup', function (): void {
    it('can get backups', function (): void {
        Backuper::backup();
        $backups = app(BackupRepository::class)->all();

        expect($backups)->toBeInstanceOf(Collection::class);
        expect($backups->count())->toBeGreaterThanOrEqual(1);
        expect($backups->first())->toBeInstanceOf(BackupDto::class);
    });

    it('can get backup by id', function (): void {
        $backup = Backuper::backup();
        $foundBackup = app(BackupRepository::class)->find($backup->id);

        expect($foundBackup)->toBeInstanceOf(BackupDto::class);
        expect($foundBackup->id)->toBe($backup->id);
        expect($foundBackup)->toEqual($backup);
    });

    it('returns null when id doesnt exist', function (): void {
        $backup = app(BackupRepository::class)->find('1234567890');
        expect($backup)->toBeNull();
    });

    it('can remove all backups', function (): void {
        Backuper::backup();

        app(BackupRepository::class)->empty();

        expect(app(BackupRepository::class)
            ->all()
            ->count())->toBe(0);
    });

    it('dispatches backup removed event', function (): void {
        Event::fake();

        $backup = Backuper::backup();
        app(BackupRepository::class)->remove($backup->id);

        Event::assertDispatched(BackupDeleted::class);
    });

    it('removes all metadata files when removing all backups', function (): void {
        Backuper::backup();

        app(BackupRepository::class)->empty();

        expect(Storage::disk('local')->files(storage_path('statamic-backup/.metadata')))->toBeEmpty();
    });

    it('can delete backup by id', function (): void {
        $backup = Backuper::backup();

        $backup = app(BackupRepository::class)->remove($backup->id);

        expect($backup)->toBeInstanceOf(BackupDto::class);
        expect(Storage::disk(config('backup.destination.disk'))->exists(
            config('backup.destination.path') . "/{$backup->name}.zip",
        ))->toBeFalse();
    });

    it('returns null and doesnt dispatch event when backup doesnt exist', function (): void {
        Event::fake();

        $backup = app(BackupRepository::class)->remove('1234567890');

        expect($backup)->toBeNull();
        Event::assertNotDispatched(BackupDeleted::class);
    });
})->group('backuprepository');
