<?php

declare(strict_types=1);

namespace Itiden\Backup\Repositories;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File as StreamableFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;

final class FileBackupRepository implements BackupRepository
{
    private string $path;

    /** @var FilesystemAdapter */
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->path = config('backup.destination.path');
        $this->filesystem = Storage::disk(config('backup.destination.disk'));
    }

    private function makeFilename(string $timestamp): string
    {
        return Str::slug(config('app.name')) . '-' . $timestamp . '.zip';
    }

    public function all(): Collection
    {
        return collect($this->filesystem->files($this->path))
            ->map(BackupDto::fromFile(...))
            ->sortByDesc('timestamp');
    }

    public function add(string $path): BackupDto
    {
        $this->filesystem->makeDirectory(path: $this->path);

        $timestamp = (string) Carbon::now()->unix();

        $this->filesystem->putFileAs(
            path: $this->path,
            file: new StreamableFile($path),
            name: $this->makeFilename($timestamp)
        );

        return $this->find($timestamp);
    }

    public function find(string $timestamp): ?BackupDto
    {
        $path = "{$this->path}/{$this->makeFilename($timestamp)}";

        if (!$this->filesystem->exists($path)) {
            return null;
        }

        return BackupDto::fromFile($path);
    }

    public function remove(string $timestamp): BackupDto
    {
        $backup = $this->find($timestamp);

        Storage::disk(config('backup.destination.disk'))->delete($backup->path);

        return $backup;
    }

    public function empty(): bool
    {
        return Storage::disk(config('backup.destination.disk'))->deleteDirectory(config('backup.destination.path'));
    }
}
