<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Pipeline;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Support\Zipper;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Events\BackupCreated;
use Itiden\Backup\Events\BackupFailed;

final class Backuper
{
    public function __construct(
        protected BackupRepository $repository,
    ) {
    }

    /**
     * Create a new backup.
     *
     * @throws Exceptions\BackupFailed
     */
    public function backup(): BackupDto
    {
        try {
            $temp_zip_path = config('backup.temp_path') . '/temp.zip';

            $zipper = Zipper::open($temp_zip_path);

            Pipeline::via('backup')
                ->send($zipper)
                ->through(config('backup.pipeline'))
                ->thenReturn();

            $password = config('backup.password');

            if ($password) {
                $zipper->encrypt($password);
            }

            $zipper->addMeta('created_at', now()->toIso8601String());

            $zipMeta = $this->resolveMetaFromZip($zipper);

            $zipper->close();

            $backup = $this->repository->add($temp_zip_path);

            $metadata = $backup->getMetadata();

            $user = auth()->user();

            if ($user) {
                $metadata->setCreatedBy($user);
            }

            $zipMeta->each(
                fn(Collection $meta, string $key): mixed => match ($key) {
                    'skipped' => $meta->each(function (string $reason, string $pipe) use ($metadata): void {
                        $metadata->addSkippedPipe(pipe: $pipe, reason: $reason);
                    }),
                },
            );

            event(new BackupCreated($backup));

            File::delete($temp_zip_path);

            $this->enforceMaxBackups();

            return $backup;
        } catch (Exception $e) {
            report($e);

            $exception = new Exceptions\BackupFailed();

            event(new BackupFailed($exception));

            throw $exception;
        }
    }

    /**
     * @return Collection<string, Collection<string|int, mixed>>
     */
    private function resolveMetaFromZip(Zipper $zip): Collection
    {
        $metadata = collect(['skipped' => collect()]);

        $zip
            ->getMeta()
            ->each(function (array|string $meta, string $key) use ($metadata): void {
                if (is_array($meta) && isset($meta['skipped'])) {
                    $metadata
                        ->get('skipped')
                        ->put($key, $meta['skipped']);
                }
            });

        return $metadata;
    }

    /**
     * Remove oldest backups when max backups is exceeded if it's present.
     */
    private function enforceMaxBackups(): void
    {
        $maxBackups = config('backup.max_backups', false);
        if (!$maxBackups) {
            return;
        }

        $backups = $this->repository->all();

        if ($backups->count() > $maxBackups) {
            $backups
                ->slice($maxBackups)
                ->each(fn(BackupDto $backup): ?BackupDto => $this->repository->remove($backup->timestamp));
        }
    }
}
