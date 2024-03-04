<?php

declare(strict_types=1);

namespace Itiden\Backup\Exceptions;

use Carbon\Carbon;
use Exception;

class BackupFailedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Backup failed ' . Carbon::now()->format('Ymd'));
    }
}
