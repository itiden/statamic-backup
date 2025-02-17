<?php

declare(strict_types=1);

namespace Itiden\Backup\Exceptions;

use Exception;
use Itiden\Backup\Enums\State;

final class ActionAlreadyInProgress extends Exception
{
    public static function fromInQueue(): self
    {
        return new self('A backup job is already queued.');
    }

    public static function fromInvalidState(State $state): self
    {
        return new self("A backup job is already in progress. Current state: {$state->value}");
    }
}
