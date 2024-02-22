<?php

declare(strict_types=1);

namespace Itiden\Backup\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Facades\Restorer;

/**
 * Restore content from a directory / backup
 */
class RestoreCommand extends Command implements PromptsForMissingInput
{
    protected $signature = 'statamic:backup:restore {path} {--force}';

    protected $description = 'Reset or restore content from a directory / backup';

    protected function promptForMissingArgumentsUsing()
    {
        return [
            'path' => 'Which filepath does your backup have?',
        ];
    }

    public function handle()
    {
        if ($this->option('force') || $this->confirm('Are you sure you want to restore your content?')) {
            Restorer::restore(BackupDto::fromAbsolutePath($this->argument('path')));
        }
    }
}
