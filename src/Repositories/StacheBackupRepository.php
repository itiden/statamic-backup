<?php

declare(strict_types=1);

namespace Itiden\Backup\Repositories;

use Carbon\Carbon;
use Illuminate\Http\File as StreamableFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Itiden\Backup\Contracts\BackupNameGenerator;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Stores\BackupStore;
use Statamic\Facades\Stache;
use Symfony\Component\Yaml\Yaml;
use Statamic\Support\Str as StatamicStr;

final class StacheBackupRepository implements BackupRepository
{
    private BackupStore $store;

    public function __construct(
        private Yaml $yaml,
    ) {
        $this->store = Stache::store('backups');
    }

    public function all(): Collection
    {
        return $this->store
            ->getItemsFromFiles()
            ->sortByDesc('timestamp');
    }

    public function find(string $timestamp): ?BackupDto
    {
        $data = $this->store->getItem($timestamp);

        if (!$data) {
            return null;
        }

        return $data;
    }

    public function add(BackupDto $dto): BackupDto
    {
        $this->store->save($dto);

        return $this->find($dto->timestamp);
    }

    public function remove(string $timestamp): BackupDto
    {
        $backup = $this->find($timestamp);

        Storage::disk(config('backup.destination.disk'))->delete($backup->path);

        $this->store->delete($backup);

        return $backup;
    }

    public function empty(): bool
    {
        $removed = Storage::disk(config('backup.destination.disk'))->deleteDirectory(config('backup.destination.path'));

        if ($removed) {
            $this->store->clear();
        }

        return $removed;
    }
}
