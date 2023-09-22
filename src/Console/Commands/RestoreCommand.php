<?php

namespace Itiden\Backup\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Itiden\Backup\Facades\Restorer;

class RestoreCommand extends Command implements PromptsForMissingInput
{
    protected $signature = 'statamic:backup:restore {path}';

    protected $description = 'Reset or restore content from a directory / backup';

    protected function promptForMissingArgumentsUsing()
    {
        return [
            'path' => 'Which filepath does your backup have?',
        ];
    }

    public function handle()
    {
        Restorer::restoreFromPath($this->argument('path'));
    }
}
