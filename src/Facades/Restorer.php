<?php

declare(strict_types=1);

namespace Itiden\Backup\Facades;

use Illuminate\Support\Facades\Facade;
use Itiden\Backup\RestorerManager;

/**
 * @method static void restoreFromPath(string $path)
 * @method static void restoreFromTimestamp(string $timestamp)
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
