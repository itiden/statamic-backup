<?php

declare(strict_types=1);

namespace Itiden\Backup\Contracts;

use Carbon\CarbonImmutable;
use Itiden\Backup\DataTransferObjects\ResolvedBackupData;

interface BackupNameResolver
{
    /**
     * Generate a filename for a backup - should include an identifier and a timestamp
     * the .zip extension will be appended automatically
     */
    public function generateFilename(CarbonImmutable $createdAt, string $id): string;

    /**
     * Parse a filename and return the resolved data
     */
    public function parseFilename(string $path): ?ResolvedBackupData;
}
