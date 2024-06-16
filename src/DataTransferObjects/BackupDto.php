<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Stores\BackupStore;
use Statamic\Facades\Stache;
use Statamic\Support\Str as StatamicStr;

final readonly class BackupDto
{
    public function __construct(
        public string $name,
        public Carbon $created_at,
        public string $size,
        public string $path,
        public string $timestamp,
        /**
         * If backup is stored on a disk, this will be the disk name
         * If null, it's assumed to be stored locally and the path is an absolute path
         */
        public ?string $disk = null,
    ) {
    }

    /**
     * Get the backup identifier
     * required(?) by the stache store
     */
    public function id(): string
    {
        return $this->timestamp;
    }

    /**
     * Get the path to the backup meta file
     */
    public function path(): string
    {
        /** @var BackupStore */
        $store = Stache::store('backups');

        return implode("", [
            $store->directory(),
            DIRECTORY_SEPARATOR,
            $this->id(),
            '.',
            $store->fileExtension()
        ]);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'timestamp' => $this->timestamp,
            'size' => $this->size,
            'path' => $this->path,
            'disk' => $this->disk,
            'created_at' => $this->created_at->toISOString(),
        ];
    }

    /**
     * Create a new BackupDto from the registry data
     */
    public static function fromArray(array $data): self
    {
        $createdAt = Carbon::parse($data['created_at']);

        return new self(
            name: $data['name'],
            created_at: $createdAt,
            size: $data['size'],
            path: $data['path'],
            timestamp: (string) $createdAt->unix(),
            disk: $data['disk'] ?? null,
        );
    }

    /**
     * Create a new BackupDto from a file path in the configured disk
     *
     * @deprecated Should not be used since it contains logic that wont work with the new backup registry
     */
    public static function fromDiskPath(string $path): static
    {
        $timestamp = str(basename($path))->afterLast('-')->before('.zip')->toString();
        $bytes = Storage::disk(config('backup.destination.disk'))->size($path);

        return new static(
            name: File::name($path),
            created_at: Carbon::createFromTimestamp($timestamp),
            size: StatamicStr::fileSizeForHumans($bytes, 2),
            path: $path,
            timestamp: $timestamp,
            disk: config('backup.destination.disk'),
        );
    }

    /**
     * Create a new BackupDto from a absolute path
     */
    public static function fromAbsolutePath(string $path): static
    {
        $timestampFromFileName = str(basename($path))->afterLast('-')->before('.zip')->toString();

        if (strlen($timestampFromFileName) !== 10 || !is_numeric($timestampFromFileName)) {
            $timestampFromFileName = null;
        }

        // Wont be the exact timestamp, but close enough I guess
        $timestamp = Carbon::createFromTimestamp(
            $timestampFromFileName
                ? $timestampFromFileName
                : File::lastModified($path)
        );

        $bytes = File::size($path);

        return new static(
            name: File::name($path),
            created_at: $timestamp,
            size: StatamicStr::fileSizeForHumans($bytes, 2),
            path: $path,
            timestamp: (string) $timestamp->unix(),
        );
    }
}
