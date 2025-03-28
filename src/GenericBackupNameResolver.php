<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Carbon\CarbonImmutable;
use Itiden\Backup\Contracts\BackupNameResolver;
use Itiden\Backup\DataTransferObjects\ResolvedBackupData;

final readonly class GenericBackupNameResolver implements BackupNameResolver
{
    public function generateFilename(CarbonImmutable $createdAt, string $id): string
    {
        return implode('', [
            str(config('app.name'))->slug(),
            '-',
            $createdAt->timestamp,
            '-',
            $id,
        ]);
    }

    public function parseFilename(string $path): ResolvedBackupData
    {
        /** @var string */
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $createdAt = CarbonImmutable::createFromTimestamp((string) str($filename)
            ->beforeLast('-')
            ->afterLast('-'));

        $id = (string) str($filename)
            ->afterLast('-')
            ->before('.zip');

        $name = (string) str($filename)
            ->remove('-' . $createdAt->timestamp)
            ->before('.zip');

        return new ResolvedBackupData(createdAt: $createdAt, id: $id, name: $name);
    }
}
