<?php

declare(strict_types=1);

namespace Itiden\Backup\Console\Commands;

use Illuminate\Console\Command;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Facades\Restorer;

use function Laravel\Prompts\{confirm, spin, info, select};

/**
 * Restore content from a directory / backup
 */
final class RestoreCommand extends Command
{
    protected $signature = 'statamic:backup:restore {--path=} {--force}';

    protected $description = 'Reset or restore content from a directory / backup';

    public function handle(BackupRepository $repo): void
    {
        /** @var BackupDto $backup */
        $backup = match (true) {
            (bool) $this->option('path') => new BackupDto(
                'not-that-important',
                basename($this->option('path')),
                now()->toImmutable(),
                (string) filesize($this->option('path')),
                $this->option('path'),
            ),
            default
                => BackupDto::fromFile(select(
                label: 'Which backup do you want to restore to?',
                scroll: 10,
                options: $repo
                    ->all()
                    ->flatMap(static fn(BackupDto $backup): array => [$backup->path => $backup->path]),
                required: true,
            )),
        };

        if (
            $this->option('force') ||
                confirm(
                    label: 'Are you sure you want to restore your content?',
                    hint: "This will overwrite your current content with state from {$backup->created_at->format(
                        'Y-m-d H:i:s',
                    )}",
                    required: true,
                )
        ) {
            spin(static fn() => Restorer::restore($backup), 'Restoring backup');

            info('Backup restored!');
        }
    }
}
