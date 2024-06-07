<?php

declare(strict_types=1);

namespace Itiden\Backup\Exceptions;

use Carbon\Carbon;
use Exception;
use Itiden\Backup\DataTransferObjects\BackupDto;

class RestoreFailedException extends Exception
{
    public function __construct(
        public BackupDto $backup
    ) {
        parent::__construct(__('statamic-backup::backup.restore_failed', ['name' => Carbon::now()->format('Ymd')]));
    }
}
