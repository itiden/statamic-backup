<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Repositories\FileBackupRepository;
use Statamic\Facades\YAML;

uses()->group('file_backup_repository');

function backupRegistry(): Collection
{
    $registryPath = config('backup.registry_directory') . '/backups.yaml';

    if (!File::exists($registryPath)) {
        return collect();
    }

    return collect(YAML::parse(File::get($registryPath)));
}

it('can can create a backup', function () {
    expect(backupRegistry()->count())->toBe(0);
    $backup = Backuper::backup();

    expect(Storage::disk(config('backup.destination.disk'))
        ->exists(config('backup.destination.path') . "/{$backup->timestamp}.zip"))->toBeTrue();

    expect(backupRegistry()->count())->toBe(1);
    expect(backupRegistry()->get($backup->timestamp))->toBe([
        'name' => $backup->name,
        'created_at' => $backup->created_at->toISOString(),
        'size' => $backup->size,
        'disk' => $backup->disk,
        'path' => $backup->path,
    ]);
});

it('can delete a backup by timestamp', function () {
    $backup = Backuper::backup();

    $backup = app(FileBackupRepository::class)->remove($backup->timestamp);

    expect(backupRegistry()->count())->toBe(0);
    expect(Storage::disk(config('backup.destination.disk'))
        ->exists(config('backup.destination.path') . "/{$backup->timestamp}.zip"))->toBeFalse();
});
