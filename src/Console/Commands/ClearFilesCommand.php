<?php

declare(strict_types=1);

namespace Itiden\Backup\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\info;

/**
 * Clear the backup temp directory
 */
class ClearFilesCommand extends Command
{
    protected $signature = 'statamic:backup:clear';

    protected $description = 'Empty the temp directory';

    public function handle()
    {
        if (!File::exists(config('backup.temp_path'))) {
            info('Backup temp directory does not exist, no need to clear it.');

            return;
        }

        File::cleanDirectory(config('backup.temp_path'));

        info('Backup temp directory cleared successfully');
    }
}
