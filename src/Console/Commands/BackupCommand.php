<?php

declare(strict_types=1);

namespace Itiden\Backup\Console\Commands;

use Illuminate\Console\Command;
use Itiden\Backup\Facades\Backuper;

/**
 * Backup site
 */
class BackupCommand extends Command
{
    protected $signature = 'statamic:backup';

    protected $description = 'Backup your stuff';

    public function handle()
    {
        $this->components->info('Backing up content');

        $backup_location = Backuper::backup();

        $this->components->info('Backup saved to ' . $backup_location->path);
    }
}
