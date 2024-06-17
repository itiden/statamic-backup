<?php

declare(strict_types=1);

namespace Itiden\Backup\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Symfony\Component\Yaml\Yaml;

final class YamlBackupRepository implements BackupRepository
{
    private const FILE = 'backups.yaml';
    private string $registryDirectory;
    private string $backupDisk;
    private string $backupDirectory;

    public function __construct(
        private Yaml $yaml,
    ) {
        $this->registryDirectory = config('backup.registry_directory');
        $this->backupDisk = config('backup.destination.disk');
        $this->backupDirectory = config('backup.destination.path');
    }

    private function getRegistryPath(): string
    {
        return "{$this->registryDirectory}/" . self::FILE;
    }

    private function getRegistryData(): Collection
    {
        if (!File::exists($this->getRegistryPath())) {
            return collect();
        }
        return collect($this->yaml->parseFile($this->getRegistryPath()));
    }

    private function writeRegistry(Collection $data): int|bool
    {
        File::ensureDirectoryExists($this->registryDirectory);

        return File::put($this->getRegistryPath(), $this->yaml->dump($data->toArray()));
    }

    public function all(): Collection
    {
        return $this->getRegistryData()
            ->map(BackupDto::fromArray(...))
            ->sortByDesc('timestamp');
    }

    public function find(string $timestamp): ?BackupDto
    {
        $data = $this->getRegistryData()->get($timestamp);

        if (!$data) {
            return null;
        }

        return BackupDto::fromArray($data);
    }

    public function add(BackupDto $backup): BackupDto
    {
        $this->writeRegistry(
            $this->getRegistryData()
                ->put($backup->id(), $backup->toArray())
        );

        return $this->find($backup->timestamp);
    }

    public function remove(string $timestamp): BackupDto
    {
        $backup = $this->find($timestamp);

        if ($backup === null) {
            throw new \Exception("Backup with timestamp {$timestamp} not found.");
        }

        Storage::disk(config('backup.destination.disk'))->delete($backup->path);

        $this->writeRegistry(
            $this->getRegistryData()
                ->forget($timestamp)
        );

        return $backup;
    }

    public function empty(): bool
    {
        $removed = Storage::disk(config('backup.destination.disk'))->deleteDirectory(config('backup.destination.path'));

        if ($removed) {
            $this->writeRegistry(collect());
        }

        return $removed;
    }
}
