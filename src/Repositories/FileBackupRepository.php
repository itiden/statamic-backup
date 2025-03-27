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
use Itiden\Backup\Events\BackupDeleted;

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

    private function makeFilename(string $timestamp, string $id): string
    {
        return implode('', [
            Str::slug(config('app.name')),
            '-',
            $timestamp,
            '-',
            $id,
            '.zip',
        ]);
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
        $id = (string) Str::ulid();

        $this->filesystem->putFileAs(
            path: $this->path,
            file: new StreamableFile($path),
            name: $this->makeFilename($timestamp, $id),
        );

        return $this->find($id);
    }

    public function find(string $id): ?BackupDto
    {
        return $this
            ->all()
            ->first(fn(BackupDto $backup): bool => $backup->id === $id);
    }

    public function remove(string $id): ?BackupDto
    {
        $backup = $this->find($id);

        if (!$backup) {
            return null;
        }

        Storage::disk(config('backup.destination.disk'))->delete($backup->path);

        event(new BackupDeleted($backup));

        return $backup;
    }

    public function empty(): bool
    {
        $this
            ->all()
            ->each(fn(BackupDto $backup): ?BackupDto => $this->remove($backup->id));
        return Storage::disk(config('backup.destination.disk'))->deleteDirectory(config('backup.destination.path'));
    }
}
