<?php

namespace Itiden\Backup\Exceptions;

use Exception;

class ManagerException extends Exception
{
    public static function clientNotFound(?string $type): static
    {
        return new static("client of type: {$type} don't exist");
    }
}
