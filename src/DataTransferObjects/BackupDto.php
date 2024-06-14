<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Statamic\Support\Str as StatamicStr;

final readonly class BackupDto
{
    public function __construct(
        public string $name,
        public Carbon $created_at,
        public string $size,
        public string $path,
        public string $timestamp,
    ) {
    }

    /**
     * Create a new BackupDto from the registry data
     */
    public static function fromRegistryData(array $data): self
    {
        $createdAt = Carbon::parse($data['created_at']);
        return new self(
            name: $data['name'],
            created_at: $createdAt,
            size: $data['size'],
            path: $data['path'],
            timestamp: (string) $createdAt->unix()
        );
    }

    /**
     * Create a new BackupDto from a file path in the configured disk
     *
     * @deprecated Should not be used since it contains logic that wont work with the new backup registry
     */
    public static function fromDiskPath(string $path): self
    {
        $timestamp = str(basename($path))->afterLast('-')->before('.zip')->toString();
        $bytes = Storage::disk(config('backup.destination.disk'))->size($path);

        return new self(
            name: File::name($path),
            created_at: Carbon::createFromTimestamp($timestamp),
            size: StatamicStr::fileSizeForHumans($bytes, 2),
            path: $path,
            timestamp: $timestamp,
        );
    }

    /**
     * Create a new BackupDto from a absolute path
     */
    public static function fromAbsolutePath(string $path): self
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

        return new self(
            name: File::name($path),
            created_at: $timestamp,
            size: StatamicStr::fileSizeForHumans($bytes, 2),
            path: $path,
            timestamp: (string) $timestamp->unix(),
        );
    }
}
