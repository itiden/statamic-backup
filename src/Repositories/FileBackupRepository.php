<?php

declare(strict_types=1);

namespace Itiden\Backup\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\File as StreamableFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Itiden\Backup\Contracts\BackupNameResolver;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Events\BackupDeleted;
use RuntimeException;

final class FileBackupRepository implements BackupRepository
{
    private string $path;

    private Filesystem $filesystem;

    public function __construct(
        private BackupNameResolver $nameResolver,
    ) {
        $this->path = Config::string('backup.destination.path');
        $this->filesystem = Storage::disk(Config::string('backup.destination.disk'));
    }

    /** {@inheritdoc} */
    public function all(): Collection
    {
        return collect($this->filesystem->allFiles($this->path))
            ->map(BackupDto::fromFile(...))
            ->whereInstanceOf(BackupDto::class)
            ->sortByDesc(static fn(BackupDto $backup) => $backup->created_at);
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

        $backup = $this->find($id);

        if (!$backup) {
            throw new RuntimeException('Failed to add backup to repository.');
        }

        return $backup;
    }

    public function find(string $id): ?BackupDto
    {
        return $this->all()->first(static fn(BackupDto $backup): bool => $backup->id === $id);
    }

    public function remove(string $id): ?BackupDto
    {
        $backup = $this->find($id);

        if (!$backup) {
            return null;
        }

        $this->filesystem->delete($backup->path);

        event(new BackupDeleted($backup));

        return $backup;
    }

    public function empty(): bool
    {
        $this->all()->each(fn(BackupDto $backup): ?BackupDto => $this->remove($backup->id));
        return $this->filesystem->deleteDirectory($this->path);
    }
}
