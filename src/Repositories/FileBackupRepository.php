<?php

declare(strict_types=1);

namespace Itiden\Backup\Repositories;

use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;

final class FileBackupRepository implements BackupRepository
{
    private string $disk;
    private string $path;

    public function __construct()
    {
        $this->disk = config('backup.destination.disk');
        $this->path = config('backup.destination.path');
    }

    private function makeFilename(string $timestamp): string
    {
        return Str::slug(config('app.name')) . '-' . $timestamp . '.zip';
    }

    public function all(): Collection
    {
        return collect(Storage::disk($this->disk)->files($this->path))
            ->map(BackupDto::fromFile(...))
            ->sortByDesc('timestamp');
    }

    public function add(string $path): BackupDto
    {
        Storage::disk($this->disk)->makeDirectory($this->path);

        $timestamp = (string) Carbon::now()->unix();

        Storage::disk($this->disk)->putFileAs(
            $this->path,
            new File($path),
            $this->makeFilename($timestamp)
        );

        return $this->find($timestamp);
    }

    public function find(string $timestamp): ?BackupDto
    {
        $path = "{$this->path}/{$this->makeFilename($timestamp)}";

        if (!Storage::disk($this->disk)->exists($path)) {
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
