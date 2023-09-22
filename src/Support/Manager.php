<?php

declare(strict_types=1);

namespace Itiden\Backup\Support;

use Itiden\Backup\Contracts\Restorer;
use Itiden\Backup\Exceptions\ManagerException;

abstract class Manager
{
    protected $clients = [];

    public function __construct()
    {
        $this->clients = collect(config('backup.backup_clients'))->flatMap(function ($client) {
            return [$client::getKey() => $client];
        })->toArray();
    }

    public function client(string $type): Restorer
    {
        if (!isset($this->clients[$type])) {
            throw ManagerException::clientNotFound($type);
        }

        return new $this->clients[$type]();
    }

    public function getClients(): array
    {
        return array_keys($this->clients);
    }
}
