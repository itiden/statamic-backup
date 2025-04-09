<?php

declare(strict_types=1);

namespace Itiden\Backup\Listeners;

use Itiden\Backup\Events\BackupDeleted;

final class BackupDeletedListener
{
    public function handle(BackupDeleted $event): void
    {
        $event->backup->getMetadata()->delete();
    }
}
