<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Pipeline;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Enums\State;
use Itiden\Backup\Events\BackupCreated;
use Itiden\Backup\Events\BackupFailed;
use Itiden\Backup\Models\Metadata;
use Itiden\Backup\Support\Zipper;
use Throwable;

use function Illuminate\Filesystem\join_paths;

final class Backuper
{
    public function __construct(
        private BackupRepository $repository,
        private StateManager $stateManager,
    ) {}

    /**
     * Create a new backup.
     *
     * @throws Exceptions\BackupFailed
     */
    public function backup(?Authenticatable $user = null): BackupDto
    {
        $lock = $this->stateManager->getLock();

        try {
            $this->stateManager->setState(State::BackupInProgress);

            $temp_zip_path = join_paths(config('backup.temp_path'), 'temp.zip');

            $zipper = Zipper::write($temp_zip_path);

            Pipeline::via('backup')
                ->send($zipper)
                ->through(config('backup.pipeline'))
                ->thenReturn();

            $password = config('backup.password');

            if ($password) {
                $zipper->encrypt($password);
            }

            $zipper->addMeta('is_backup', 'true');
            $zipper->addMeta('version', '1');
            $zipper->addMeta('created_at', now()->toIso8601String());

            $zipper->close();

            $backup = $this->repository->add($temp_zip_path);

            $metadata = static::addMetaFromZipToBackupMeta($temp_zip_path, $backup);

            if ($user) {
                $metadata->setCreatedBy($user);
            }

            event(new BackupCreated($backup));

            File::delete($temp_zip_path);

            $this->enforceMaxBackups();

            $this->stateManager->setState(State::BackupCompleted);

            return $backup;
        } catch (Throwable $e) {
            $exception = new Exceptions\BackupFailed(previous: $e);

            event(new BackupFailed($exception));

            $this->stateManager->setState(State::BackupFailed);

            throw $exception;
        } finally {
            $lock->release();
        }
    }

    public static function addMetaFromZipToBackupMeta(string $pathToZip, BackupDto $backup): Metadata
    {
        $metadata = $backup->getMetadata();
        $zip = Zipper::read($pathToZip);
        $zip
            ->getMeta()
            ->filter(static fn(mixed $data) => is_array($data) && isset($data['skipped']))
            ->map(static fn(array $data) => $data['skipped'])
            ->each(static fn(string $reason, string $pipe) => $metadata->addSkippedPipe($pipe, $reason));

        $zip->close();

        return $metadata;
    }

    /**
     * Remove oldest backups when max backups is exceeded if it's present.
     */
    public function enforceMaxBackups(): void
    {
        $maxBackups = config('backup.max_backups', false);
        if (!$maxBackups) {
            return;
        }

        $backups = $this->repository->all();

        if ($backups->count() > $maxBackups) {
            $backups
                ->slice($maxBackups)
                ->each(fn(BackupDto $backup): ?BackupDto => $this->repository->remove($backup->id));
        }
    }
}
