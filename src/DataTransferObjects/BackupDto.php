<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Statamic\Support\Str as StatamicStr;

readonly class BackupDto
{
    public function __construct(
        public string $name,
        public string $created_at,
        public string $size,
        public string $path,
        public string $timestamp,
    ) {
    }

    /**
     * Create a new BackupDto from a file path
     */
    public static function fromFile(string $path): self
    {
        $timestamp = Str::before(Str::after(basename($path), '-'), '.zip');
        $bytes = Storage::disk(config('backup.destination.disk'))->size($path);

        return new self(
            name: File::name($path),
            created_at: Carbon::createFromTimestamp($timestamp)->format('Y-m-d H:i:s'),
            size: StatamicStr::fileSizeForHumans($bytes, 2),
            path: $path,
            timestamp: $timestamp,
        );
    }
}
