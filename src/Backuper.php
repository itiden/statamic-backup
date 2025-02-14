<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Exception;
use Illuminate\Support\Facades\Pipeline;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Support\Zipper;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Enums\State;
use Itiden\Backup\Events\BackupCreated;
use Itiden\Backup\Events\BackupFailed;
use Throwable;

final class Backuper
{
    public function __construct(
        private BackupRepository $repository,
        private StateManager $stateManager
    ) {
    }

    public function canBackup(): bool
    {
        $state = $this->stateManager->getState();

        return !in_array(
            needle: $state,
            haystack: [State::BackupInProgress, State::RestoreInProgress]
        );
    }

    /**
     * Create a new backup.
     *
     * @throws Exceptions\BackupFailed
     */
    public function backup(): BackupDto
    {
        $state = $this->stateManager->getState();

        if (!$this->canBackup()) {
            throw new Exception("Cannot start backup while in state \"{$state->value}\"");
        }


        try {
            $this->stateManager->setState(State::BackupInProgress);

            $temp_zip_path = config('backup.temp_path') . '/temp.zip';

            $zipper = Zipper::open($temp_zip_path);

            Pipeline::via('backup')
                ->send($zipper)
                ->through(config('backup.pipeline'))
                ->thenReturn();

            if ($password = config('backup.password')) {
                $zipper->encrypt($password);
            }

            $zipper->addMeta('created_at', now()->toIso8601String());

            $zipMeta = $this->resolveMetaFromZip($zipper);

            $zipper->close();

            $backup = $this->repository->add($temp_zip_path);

            $metadata = $backup->getMetadata();

            if ($user = auth()->user()) {
                $metadata->setCreatedBy($user);
            }

            $zipMeta->each(fn ($meta, $key) => match ($key) {
                'skipped' => $meta->each(fn (string $reason, string $pipe) => $metadata->addSkippedPipe($pipe, $reason)),
            });

            event(new BackupCreated($backup));

            @unlink($temp_zip_path);

            $this->enforceMaxBackups();

            $this->stateManager->setState(State::BackupCompleted);

            return $backup;
        } catch (Throwable $e) {
            report($e);

            $exception = new Exceptions\BackupFailed(previous: $e);

            event(new BackupFailed($exception));

            $this->stateManager->setState(State::BackupFailed);

            throw $exception;
        }
    }

    private function resolveMetaFromZip(Zipper $zip)
    {
        $metadata = collect([
            'skipped' => collect(),
        ]);

        $zip->getMeta()->each(function ($meta, $key) use ($metadata) {
            if (isset($meta['skipped'])) {
                $metadata->get('skipped')->put($key, $meta['skipped']);
            }
        });

        return $metadata;
    }

    /**
     * Remove oldest backups when max backups is exceeded if it's present.
     */
    private function enforceMaxBackups(): void
    {
        if (!$max_backups = config('backup.max_backups', false)) {
            return;
        }

        $backups = $this->repository->all();

        if ($backups->count() > $max_backups) {
            $backups->slice($max_backups)->each(function ($backup) {
                $this->repository->remove($backup->timestamp);
            });
        }
    }
}
