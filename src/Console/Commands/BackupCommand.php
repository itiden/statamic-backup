<?php

declare(strict_types=1);

namespace Itiden\Backup\Console\Commands;

use Illuminate\Console\Command;
use Itiden\Backup\Facades\Backuper;

use function Laravel\Prompts\{info, spin};

/**
 * Backup site
 */
class BackupCommand extends Command
{
    protected $signature = 'statamic:backup';

    protected $description = 'Run the backup pipeline';

    public function handle()
    {
        $backup = spin(fn () => Backuper::backup(), 'Backing up...');

        info('Backup saved to ' . $backup->path);
    }
}
