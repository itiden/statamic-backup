<?php

namespace Itiden\Backup\Facades;

use Illuminate\Support\Facades\Facade;
use Itiden\Backup\RestorerManager;

/**
 * @method static string restoreFromPath(string $path)
 *
 * @see \Itiden\Backup\RestorerManager
 */
class Restorer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RestorerManager::class;
    }
}
