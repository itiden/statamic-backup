<?php

declare(strict_types=1);

namespace Itiden\Backup\Facades;

use Illuminate\Support\Facades\Facade;
use Itiden\Backup\Backuper as BackuperService;

/**
 * @mixin \Itiden\Backup\Backuper
 */
final class Backuper extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return BackuperService::class;
    }
}
