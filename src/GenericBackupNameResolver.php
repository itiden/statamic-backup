<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Carbon\CarbonImmutable;
use Itiden\Backup\Contracts\BackupNameResolver;
use Itiden\Backup\DataTransferObjects\ResolvedBackupData;

final readonly class GenericBackupNameResolver implements BackupNameResolver
{
    private const Separator = '---';

    public function generateFilename(CarbonImmutable $createdAt, string $id): string
    {
        return implode(static::Separator, [
            str(config('app.name'))->slug(),
            $createdAt->timestamp,
            $id,
        ]);
    }

    public function parseFilename(string $path): ?ResolvedBackupData
    {
        /** @var string */
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $parts = explode(static::Separator, $filename);

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
