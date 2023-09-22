<?php

declare(strict_types=1);

namespace Itiden\Backup\Facades;

use Illuminate\Support\Facades\Facade;
use Itiden\Backup\BackuperManager;

/**
 * @method static \Itiden\Backup\DataTransferObjects\BackupDto backup()
 * @method static \Illuminate\Support\Collection getBackups()
 *
 * @see \Itiden\Backup\BackuperManager
 */
class Backuper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BackuperManager::class;
    }
}
