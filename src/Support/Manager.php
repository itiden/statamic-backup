<?php

declare(strict_types=1);

namespace Itiden\Backup\Support;

use Itiden\Backup\Contracts\BackupDriver;
use Itiden\Backup\Exceptions\ManagerException;
use Itiden\Backup\Contracts\Repositories\BackupRepository;

abstract class Manager
{
    protected array $container = [];
    protected array $drivers = [];

    public function __construct(
        protected BackupRepository $repository
    ) {
        $this->container = collect(config('backup.backup_drivers'))->flatMap(function ($client) {
            return [$client::getKey() => $client];
        })->toArray();
    }

    /**
     * Get the driver instance.
     */
    public function driver(string $type): BackupDriver
    {
        return $this->drivers[$type] ?? $this->createDriver($type);
    }

    /**
     * Create the driver instance.
     */
    protected function createDriver(string $type): BackupDriver
    {
        if (!array_key_exists($type, $this->container)) {
            throw ManagerException::clientNotFound($type);
        }

        $this->drivers[$type] = new $this->container[$type]();

        return $this->drivers[$type];
    }

    /**
     * Get all of the created "drivers".
     */
    public function getDrivers(): array
    {
        return array_keys($this->container);
    }
}
