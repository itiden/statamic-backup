<?php

declare(strict_types=1);

namespace Itiden\Backup\Enums;

enum State: string
{
    case Idle = 'idle';

    case BackupInProgress = 'backup_in_progress';
    case RestoreInProgress = 'restore_in_progress';

    case BackupCompleted = 'backup_completed';
    case RestoreCompleted = 'restore_completed';

    case BackupFailed = 'backup_failed';
    case RestoreFailed = 'restore_failed';
}
