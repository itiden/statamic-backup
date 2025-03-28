<?php

declare(strict_types=1);

namespace Itiden\Backup\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File as StreamableFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Itiden\Backup\Contracts\BackupNameResolver;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Events\BackupDeleted;

final class FileBackupRepository implements BackupRepository
{
    private string $path;

    /** @var FilesystemAdapter */
    private Filesystem $filesystem;

    public function __construct(
        private BackupNameResolver $nameResolver,
    ) {
        $this->path = config('backup.destination.path');
        $this->filesystem = Storage::disk(config('backup.destination.disk'));
    }

    public function all(): Collection
    {
        return collect($this->filesystem->allFiles($this->path))
            ->map(BackupDto::fromFile(...))
            ->whereInstanceOf(BackupDto::class)
            ->sortByDesc(fn(BackupDto $backup) => $backup->created_at);
    }

    public function add(string $path): BackupDto
    {
        $this->filesystem->makeDirectory(path: $this->path);

        $id = (string) Str::ulid();

        $this->filesystem->putFileAs(
            path: $this->path,
            file: new StreamableFile($path),
            name: (string) str($this->nameResolver->generateFilename(CarbonImmutable::now(), $id))->finish('.zip'),
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
