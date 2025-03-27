<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Models\Metadata;
use Statamic\Support\Str as StatamicStr;

final readonly class BackupDto
{
    public function __construct(
        public string $id,
        public string $name,
        public CarbonImmutable $created_at,
        public string $size,
        public string $path,
    ) {}

    public function getMetadata(): Metadata
    {
        return new Metadata($this);
    }

    /**
     * Create a new BackupDto from a file path in the configured disk
     */
    public static function fromFile(string $path): static
    {
        [$createtAt, $id, $name] = static::extractValuesFromPath($path);

        $bytes = Storage::disk(config('backup.destination.disk'))->size($path);

        return new static(
            id: $id,
            name: $name,
            created_at: $createtAt,
            size: StatamicStr::fileSizeForHumans($bytes, 2),
            path: $path,
        );
    }

    /**
     * Create a new BackupDto from a absolute path
     */
    public static function fromAbsolutePath(string $path): static
    {
        [$createdAt, $id, $name] = static::extractValuesFromPath($path);

        $bytes = File::size($path);

        return new static(
            id: $id,
            name: $name,
            created_at: $createdAt,
            size: StatamicStr::fileSizeForHumans($bytes, 2),
            path: $path,
        );
    }

    private static function extractValuesFromPath(string $path): array
    {
        $timestamp = CarbonImmutable::createFromTimestamp(str(basename($path))
            ->beforeLast('-')
            ->afterLast('-')
            ->toString());

        $id = str(basename($path))
            ->afterLast('-')
            ->before('.zip')
            ->toString();

        $name = File::name($path);

        return [$timestamp, $id, $name];
    }
}
