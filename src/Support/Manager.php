<?php

declare(strict_types=1);

namespace Itiden\Backup\Support;

use Itiden\Backup\Contracts\Restorer;
use Itiden\Backup\Exceptions\ManagerException;

abstract class Manager
{
    protected array $container = [];
    protected array $drivers = [];

    public function __construct()
    {
        $this->container = collect(config('backup.backup_drivers'))->flatMap(function ($client) {
            return [$client::getKey() => $client];
        })->toArray();
    }

    public function driver(string $type): Restorer
    {
        return $this->drivers[$type] ?? $this->createDriver($type);
    }

    protected function createDriver(string $type): Restorer
    {
        if (!array_key_exists($type, $this->container)) {
            throw ManagerException::clientNotFound($type);
        }

        $this->drivers[$type] = new $this->container[$type]();

        return $this->drivers[$type];
    }

    public function getDrivers(): array
    {
        return array_keys($this->container);
    }
}
