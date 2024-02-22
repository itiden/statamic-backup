<?php

declare(strict_types=1);

namespace Itiden\Backup\Exceptions;

use Carbon\Carbon;
use Exception;

class BackupFailedException extends Exception
{
    public function __construct(Exception $e)
    {
        parent::__construct('Backup failed ' . Carbon::now()->format('Ymd'), 0, $e);
    }
}
