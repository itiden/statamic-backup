<?php

declare(strict_types=1);

namespace Itiden\Backup\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Facades\Restorer;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;

/**
 * Restore content from a directory / backup
 */
class RestoreCommand extends Command implements PromptsForMissingInput
{
    protected $signature = 'statamic:backup:restore {--path} {--force}';

    protected $description = 'Reset or restore content from a directory / backup';

    protected function promptForMissingArgumentsUsing()
    {
        return [
            'path' => 'Which filepath does your backup have?',
        ];
    }

    public function handle(BackupRepository $repo)
    {
        /* @var BackupDto $backup */
        $backup = match (true) {
            $this->hasOption('path') => BackupDto::fromAbsolutePath($this->option('path')),
            default => BackupDto::fromFile(select(
                'Which backup do you want to restore to?',
                $repo->all()->flatMap(
                    fn (BackupDto $backup) => [$backup->path => $backup->path]
                )
            )),
        };

        if (
            $this->option('force')
            || confirm(
                label: "Are you sure you want to restore your content?",
                hint: "This will overwrite your current content with state from {$backup->created_at->format('Y-m-d H:i:s')}"
            )
        ) {
            spin(fn () => Restorer::restore($backup), 'Restoring backup');
        }
    }
}
