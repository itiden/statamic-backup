<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

readonly class BackupDto
{
    public function __construct(
        public string $name,
        public int $size,
        public string $path,
        public string $timestamp,
    ) {
    }

    public static function fromFile(string $path): self
    {
        $timestamp = Str::before(Str::after(basename($path), '-'), '.zip');

        return new self(
            name: Carbon::createFromTimestamp($timestamp)->format('Y-m-d H:i:s'),
            size: Storage::disk(config('backup.destination.disk'))->size($path),
            path: $path,
            timestamp: $timestamp,
        );
    }
}
