<?php

declare(strict_types=1);

namespace Itiden\Backup\Exceptions;

use Exception;

final class ActionAlreadyInProgress extends Exception
{
    public function __construct()
    {
        parent::__construct('A backup job is already queued.');
    }
}
