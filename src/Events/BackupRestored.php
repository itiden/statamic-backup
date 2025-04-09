<?php

declare(strict_types=1);

namespace Itiden\Backup\Events;

use Itiden\Backup\DataTransferObjects\BackupDto;

final readonly class BackupRestored
{
    public function __construct(
        public BackupDto $backup,
    ) {}
}
