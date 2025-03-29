<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Carbon\CarbonImmutable;

final readonly class ResolvedBackupData
{
    public function __construct(
        public CarbonImmutable $createdAt,
        public string $id,
        public string $name,
    ) {}
}
