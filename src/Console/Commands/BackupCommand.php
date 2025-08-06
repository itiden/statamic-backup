<?php

declare(strict_types=1);

namespace Itiden\Backup\Console\Commands;

use Illuminate\Console\Command;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Facades\Backuper;

use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;

/**
 * Backup site
 */
final class BackupCommand extends Command
{
    // @mago-expect lint:strictness/require-property-type
    protected $signature = 'statamic:backup';

    // @mago-expect lint:strictness/require-property-type
    protected $description = 'Run the backup pipeline';

    public function handle(): void
    {
        $backup = spin(Backuper::backup(...), 'Backing up...');

        info('Backup saved to ' . $backup->path);
    }
}
