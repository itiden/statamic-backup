<?php

namespace Itiden\Backup\Console\Commands;

use Illuminate\Console\Command;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Facades\Restorer;

class BackupCommand extends Command
{
    protected $signature = 'statamic:backup';

    protected $description = 'Backup your stuff';

    public function handle()
    {
        $this->components->info('Backing up content');

        $backup_location = Backuper::backup();

        $this->components->info('Backup saved to ' . $backup_location);
    }
}
