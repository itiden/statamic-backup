<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Support\Zipper;

uses()->group('backuper');

it('can backup', function () {
    $backup = Backuper::backup();

    expect($backup)->toBeInstanceOf(BackupDto::class);

    expect(Storage::disk(config('backup.destination.disk'))
        ->exists(config('backup.destination.path') . "/{$backup->name}.zip"))->toBeTrue();
});

it('backups correct files', function () {
    $backup = Backuper::backup();

    $unzipped = config('backup.temp_path') . '/unzipped';
    Zipper::make(
        Storage::disk(config('backup.destination.disk'))
            ->path($backup->path),
        true
    )
        ->extractTo(
            $unzipped,
            config('backup.password'),
        );

    expect(File::allFiles($unzipped)[0]->getRelativePathname())
        ->toEqual('content/collections/pages/homepage.yaml');
});

it('can get backups', function () {
    Backuper::backup();
    $backups = Backuper::getBackups();

    expect($backups)->toBeInstanceOf(Collection::class);
    expect($backups->count())->toBeGreaterThanOrEqual(1);
    expect($backups->first())->toBeInstanceOf(BackupDto::class);
});

it('can get backup by timestamp', function () {
    $backup = Backuper::backup();
    $backupByTimestamp = Backuper::getBackup($backup->timestamp);

    expect($backupByTimestamp)->toBeInstanceOf(BackupDto::class);
    expect($backupByTimestamp->timestamp)->toBe($backup->timestamp);
    expect($backupByTimestamp)->toEqual($backup);
});

it('can clear backups', function () {
    Backuper::backup();
    Backuper::clearBackups();

    expect(Backuper::getBackups()->count())->toBe(0);
});

it('can enforce max backups', function () {
    Backuper::backup();

    config()->set('backup.limit', 5);

    expect(Backuper::getBackups()->count())->toBeLessThanOrEqual(5);
})->repeat(10);

it('can delete backup by timestamp', function () {
    $backup = Backuper::backup();

    $backup = Backuper::deleteBackup($backup->timestamp);

    expect($backup)->toBeInstanceOf(BackupDto::class);
    expect(Storage::disk(config('backup.destination.disk'))
        ->exists(config('backup.destination.path') . "/{$backup->name}.zip"))->toBeFalse();
});
