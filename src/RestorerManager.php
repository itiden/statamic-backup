<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Itiden\Backup\Support\Manager;

class RestorerManager extends Manager
{
    public function restoreFromPath(string $path): void
    {
        collect($this->getClients())->each(function ($key) use ($path) {
            $this->client($key)->restore("{$path}/{$key}");
        });
    }
}
