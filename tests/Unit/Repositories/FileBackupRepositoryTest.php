<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Repositories\YamlBackupRepository;
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
    app()->bind(BackupRepository::class, YamlBackupRepository::class);
    expect(backupRegistry()->count())->toBe(0);
    $backup = Backuper::backup();

    expect(Storage::disk(config('backup.destination.disk'))
        ->exists(config('backup.destination.path') . "/{$backup->timestamp}.zip"))->toBeTrue();

    expect(backupRegistry()->count())->toBe(1);
    expect(backupRegistry()->get($backup->timestamp))->toBe([
        'name' => $backup->name,
        'timestamp' => $backup->timestamp,
        'size' => $backup->size,
        'path' => $backup->path,
        'disk' => $backup->disk,
        'created_at' => $backup->created_at->toISOString(),
    ]);
});

it('can delete a backup by timestamp', function () {
    app()->bind(BackupRepository::class, YamlBackupRepository::class);
    $backup = Backuper::backup();

    $backup = app(BackupRepository::class)->remove($backup->timestamp);

    expect(backupRegistry()->count())->toBe(0);
    expect(Storage::disk(config('backup.destination.disk'))
        ->exists(config('backup.destination.path') . "/{$backup->timestamp}.zip"))->toBeFalse();
});
