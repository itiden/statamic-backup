<?php

declare(strict_types=1);

namespace Itiden\Backup\Console\Commands;

use Illuminate\Console\Command;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Facades\Backuper;

use function Laravel\Prompts\{info, spin};

/**
 * Backup site
 */
final class BackupCommand extends Command
{
    protected $signature = 'statamic:backup';

    protected $description = 'Run the backup pipeline';

    public function handle(): void
    {
        $backup = spin(fn(): BackupDto => Backuper::backup(), 'Backing up...');

        info('Backup saved to ' . $backup->path);
    }
}
