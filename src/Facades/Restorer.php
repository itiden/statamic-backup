<?php

declare(strict_types=1);

namespace Itiden\Backup\Facades;

use Illuminate\Support\Facades\Facade;
use Itiden\Backup\Restorer as RestorerService;

/**
 * @method static void restore(\Itiden\Backup\DataTransferObjects\BackupDto $backup)
 * @method static void restoreFromTimestamp(string $timestamp)
 *
 * @see \Itiden\Backup\Restorer
 */
class Restorer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RestorerService::class;
    }
}
