<?php

use Illuminate\Support\Collection;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Facades\Backuper;

uses()->group('backup_repositories');

// TODO: Bind the backup repository to each of the repositories

it('can get backups', function (string $repository) {
    Backuper::backup();
    $backups = app($repository)->all();

    expect($backups)->toBeInstanceOf(Collection::class);
    expect($backups->count())->toBeGreaterThanOrEqual(1);
    expect($backups->first())->toBeInstanceOf(BackupDto::class);
})->with('backup_repositories');

it('can get backup by timestamp', function (string $repository) {
    $backup = Backuper::backup();
    $backupByTimestamp = app($repository)->find($backup->timestamp);

    expect($backupByTimestamp)->toBeInstanceOf(BackupDto::class);
    expect($backupByTimestamp->timestamp)->toBe($backup->timestamp);
    expect($backupByTimestamp)->toEqual($backup);
})->with('backup_repositories');

it("returns null when timestamp doesnt exist", function (string $repository) {
    $backup = app($repository)->find('1234567890');
    expect($backup)->toBeNull();
})->with('backup_repositories');

it('can remove all backups', function (string $repository) {
    Backuper::backup();

    app($repository)->empty();

    expect(app($repository)->all()->count())->toBe(0);
})->with('backup_repositories');


it('can delete backup by timestamp', function (string $repository) {
    $backup = Backuper::backup();

    $backup = app($repository)->remove($backup->timestamp);

    expect($backup)->toBeInstanceOf(BackupDto::class);
    expect(app($repository)->find($backup->timestamp))->toBeNull();
    expect(app($repository)->all()->count())->toBe(0);
})->with('backup_repositories');

it('will throw exception when trying to remove non-existing backup', function (string $repository) {
    app($repository)->remove('1234567890');
})->throws(Exception::class)->with('backup_repositories');
