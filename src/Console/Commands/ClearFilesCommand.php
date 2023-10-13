<?php

declare(strict_types=1);

namespace Itiden\Backup\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Clear the backup temp directory
 */
class ClearFilesCommand extends Command
{
    protected $signature = 'statamic:backup:clear';

    protected $description = 'Empty the temp directory';

    public function handle()
    {
        File::cleanDirectory(config('backup.temp_path'));

        $this->components->info('Backup temp directory cleared successfully');
    }
}
