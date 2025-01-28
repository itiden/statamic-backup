<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Models\Metadata;
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

    public function getMetadata(): Metadata
    {
        return new Metadata($this);
    }

    /**
     * Create a new BackupDto from a file path in the configured disk
     */
    public static function fromFile(string $path): self
    {
        $timestamp = str(basename($path))
            ->afterLast('-')
            ->before('.zip')
            ->toString();
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
        $timestamp = str(basename($path))
            ->afterLast('-')
            ->before('.zip')
            ->toString();
        $bytes = File::size($path);

        return new self(
            name: File::name($path),
            created_at: Carbon::createFromTimestamp($timestamp),
            size: StatamicStr::fileSizeForHumans($bytes, 2),
            path: $path,
            timestamp: $timestamp,
        );
    }
}
