<?php

declare(strict_types=1);

namespace Itiden\Backup\Facades;

use Illuminate\Support\Facades\Facade;
use Itiden\Backup\Restorer as RestorerService;

/**
 * @mixin \Itiden\Backup\Restorer
 */
final class Restorer extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return RestorerService::class;
    }
}
