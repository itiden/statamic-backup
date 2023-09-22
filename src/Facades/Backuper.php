<?php

namespace Itiden\Backup\Facades;

use Illuminate\Support\Facades\Facade;
use Itiden\Backup\BackuperManager;

/**
 * @method static string backup()
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
