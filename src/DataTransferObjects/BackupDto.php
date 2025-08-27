<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\BackupNameResolver;
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
    public static function fromFile(string $path): ?static
    {
        $values = app(BackupNameResolver::class)->parseFilename($path);

        if (!$values) {
            return null;
        }

        $bytes = Storage::disk(Config::string('backup.destination.disk'))->size($path);

        return new static(
            id: $values->id,
            name: $values->name,
            created_at: $values->createdAt,
            size: StatamicStr::fileSizeForHumans($bytes, 2),
            path: $path,
        );
    }
}
