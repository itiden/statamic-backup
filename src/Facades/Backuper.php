<?php

declare(strict_types=1);

namespace Itiden\Backup\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Itiden\Backup\BackuperManager;
use Itiden\Backup\DataTransferObjects\BackupDto;

/**
 * @method static BackupDto backup()
 * @method static Collection<BackupDto> getBackups()
 * @method static BackupDto getBackup(string $backupName)
 * @method static BackupDto deleteBackup(string $backupName)
 * @method static bool clearBackups()
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
