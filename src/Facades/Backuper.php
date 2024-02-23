<?php

declare(strict_types=1);

namespace Itiden\Backup\Facades;

use Illuminate\Support\Facades\Facade;
use Itiden\Backup\Backuper as BackuperService;
use Itiden\Backup\DataTransferObjects\BackupDto;

/**
 * @method static BackupDto backup()
 *
 * @see \Itiden\Backup\Backuper
 */
class Backuper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BackuperService::class;
    }
}
