<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Events\BackupDeleted;
use Itiden\Backup\Facades\Backuper;

describe('repository:backup', function () {
    it('can get backups', function () {
        Backuper::backup();
        $backups = app(BackupRepository::class)->all();

        expect($backups)->toBeInstanceOf(Collection::class);
        expect($backups->count())->toBeGreaterThanOrEqual(1);
        expect($backups->first())->toBeInstanceOf(BackupDto::class);
    });

    it('can get backup by timestamp', function () {
        $backup = Backuper::backup();
        $backupByTimestamp = app(BackupRepository::class)->find($backup->timestamp);

        expect($backupByTimestamp)->toBeInstanceOf(BackupDto::class);
        expect($backupByTimestamp->timestamp)->toBe($backup->timestamp);
        expect($backupByTimestamp)->toEqual($backup);
    });

    it('returns null when timestamp doesnt exist', function () {
        $backup = app(BackupRepository::class)->find('1234567890');
        expect($backup)->toBeNull();
    });

    it('can remove all backups', function () {
        Backuper::backup();

        app(BackupRepository::class)->empty();

        expect(app(BackupRepository::class)
            ->all()
            ->count())->toBe(0);
    });

    it('dispatches backup removed event', function () {
        Event::fake();

        $backup = Backuper::backup();
        app(BackupRepository::class)->remove($backup->timestamp);

        Event::assertDispatched(BackupDeleted::class);
    });

    it('removes all metadata files when removing all backups', function () {
        Backuper::backup();

        app(BackupRepository::class)->empty();

        expect(Storage::disk('local')->files(storage_path('statamic-backup/.metadata')))->toBeEmpty();
    });

    it('can delete backup by timestamp', function () {
        $backup = Backuper::backup();

        $backup = app(BackupRepository::class)->remove($backup->timestamp);

        expect($backup)->toBeInstanceOf(BackupDto::class);
        expect(Storage::disk(config('backup.destination.disk'))->exists(
            config('backup.destination.path') . "/{$backup->name}.zip",
        ))->toBeFalse();
    });
})->group('backuprepository');
