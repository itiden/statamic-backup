<?php

declare(strict_types=1);

namespace Itiden\Backup\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\info;

/**
 * Clear the backup temp directory
 *
 * @mago-expect lint:strictness/require-property-type
 */
final class ClearFilesCommand extends Command
{
    protected $signature = 'statamic:backup:temp-clear';

    protected $description = 'Clear the backup temp directory';

    public function handle(): void
    {
        $path = Config::string('backup.temp_path');

        if (!File::exists(path: $path)) {
            info('Backup temp directory does not exist, no need to clear it.');

            return;
        }

        File::cleanDirectory(directory: $path);

        info('Backup temp directory cleared successfully');
    }
}
