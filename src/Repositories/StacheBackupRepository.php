<?php

declare(strict_types=1);

namespace Itiden\Backup\Repositories;

use Carbon\Carbon;
use Illuminate\Http\File as StreamableFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\BackupNameGenerator;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Statamic\Facades\Stache;
use Symfony\Component\Yaml\Yaml;
use Statamic\Support\Str as StatamicStr;

final class StacheBackupRepository implements BackupRepository
{
    private string $backupDisk;
    private string $backupDirectory;

    public function __construct(
        private Yaml $yaml,
    ) {
        $this->backupDisk = config('backup.destination.disk');
        $this->backupDirectory = config('backup.destination.path');
    }

    public function all(): Collection
    {
        return Stache::store('backups')
            ->getItemsFromFiles()
            ->sortByDesc('timestamp');
    }

    public function find(string $timestamp): ?BackupDto
    {
        $data = Stache::store('backups')->getItem($timestamp);

        if (!$data) {
            return null;
        }

        return $data;
    }

    public function add(string $path): BackupDto
    {
        Storage::disk($this->backupDisk)->makeDirectory($this->backupDirectory);

        $createdAt = Carbon::now();

        $path = Storage::disk($this->backupDisk)->putFileAs(
            $this->backupDirectory,
            new StreamableFile($path),
            $createdAt->unix() . '.zip'
        );

        Stache::store('backups')->save(new BackupDto(
            name: app(BackupNameGenerator::class)->generate($createdAt),
            created_at: $createdAt,
            size: StatamicStr::fileSizeForHumans(Storage::disk($this->backupDisk)->size($path), 2),
            timestamp: (string) $createdAt->unix(),
            path: $path,
            disk: $this->backupDisk,
        ));

        return $this->find((string) $createdAt->unix());
    }

    public function remove(string $timestamp): BackupDto
    {
        $backup = $this->find($timestamp);

        if ($backup === null) {
            throw new \Exception("Backup with timestamp {$timestamp} not found.");
        }

        Storage::disk(config('backup.destination.disk'))->delete($backup->path);

        Stache::store('backups')->delete($backup);

        return $backup;
    }

    public function empty(): bool
    {
        $removed = Storage::disk(config('backup.destination.disk'))->deleteDirectory(config('backup.destination.path'));

        if ($removed) {
            Stache::store('backups')->clear();
        }

        return $removed;
    }
}
