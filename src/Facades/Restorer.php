<?php

declare(strict_types=1);

namespace Itiden\Backup\Facades;

use Illuminate\Support\Facades\Facade;
use Itiden\Backup\RestorerManager;

/**
 * @method static void restore(string $path)
 * @method static void restoreFromTimestamp(string $timestamp)
 * @method static void restoreFromArchive(string $path)
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
