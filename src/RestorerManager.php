<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Itiden\Backup\Support\Manager;

class RestorerManager extends Manager
{
    public function restoreFromPath(string $path): void
    {
        collect($this->getDrivers())->each(function ($key) use ($path) {
            $this->driver($key)->restore("{$path}/{$key}");
        });
    }
}
