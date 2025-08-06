<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Itiden\Backup\Contracts\BackupNameResolver;
use Itiden\Backup\DataTransferObjects\ResolvedBackupData;

final readonly class GenericBackupNameResolver implements BackupNameResolver
{
    private const SEPARATOR = '---';

    public function generateFilename(CarbonImmutable $createdAt, string $id): string
    {
        return implode(static::SEPARATOR, [
            (string) str(Config::string('app.name'))->slug(),
            (string) $createdAt->timestamp,
            $id,
        ]);
    }

    public function parseFilename(string $path): ?ResolvedBackupData
    {
        /** @var string */
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $parts = explode(static::SEPARATOR, $filename);

        if (count($parts) !== 3)
            return null;

        [$name, $createdAt, $id] = $parts;

        return new ResolvedBackupData(
            createdAt: CarbonImmutable::createFromTimestamp($createdAt),
            id: $id,
            name: $name,
        );
    }
}
