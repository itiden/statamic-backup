<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Pipeline;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Enums\State;
use Itiden\Backup\Events\BackupCreated;
use Itiden\Backup\Events\BackupFailed;
use Itiden\Backup\Models\Metadata;
use Itiden\Backup\Support\Zipper;
use RuntimeException;
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
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }

        ignore_user_abort(true);

        $lock = $this->stateManager->getLock();

        $temp_zip_path = join_paths(Config::string('backup.temp_path'), 'temp.zip');

        try {
            $this->stateManager->setState(State::BackupInProgress);

            $completed = false;

            register_shutdown_function(function () use (&$completed, $temp_zip_path, $lock): void {
                if ($completed) {
                    return;
                }

                $error = error_get_last();

                // Only treat true fatal errors as a "killed mid-backup" scenario.
                if (
                    $error === null
                    || !in_array(
                        $error['type'],
                        [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR],
                        strict: true,
                    )
                ) {
                    return;
                }

                Log::error('backup failed due to timeout', $error);

                if (File::exists($temp_zip_path)) {
                    File::delete($temp_zip_path);
                }

                // Ensure the lock doesn't remain held indefinitely after a fatal error.
                $lock->forceRelease();

                $this->stateManager->setState(State::BackupFailed);
            });

            $zipper = Zipper::write($temp_zip_path);

            Pipeline::via('backup')->send($zipper)->through(Config::array('backup.pipeline'))->thenReturn();

            /** @var string|null */
            $password = Config::get('backup.password');

            if ($password) {
                $zipper->encrypt($password);
            }

            $zipper->addMeta('is_backup', 'true');
            $zipper->addMeta('version', '1');
            $zipper->addMeta('created_at', now()->toIso8601String());

            $zipper->close();

            if (!Zipper::verify($temp_zip_path)) {
                File::delete($temp_zip_path);

                throw new RuntimeException('Zip verification failed — the backup archive is invalid.');
            }

            $backup = $this->repository->add($temp_zip_path);

            $metadata = static::addMetaFromZipToBackupMeta($temp_zip_path, $backup);

            if ($user) {
                $metadata->setCreatedBy($user);
            }

            event(new BackupCreated($backup));

            File::delete($temp_zip_path);

            $this->enforceMaxBackups();

            $this->stateManager->setState(State::BackupCompleted);

            Log::info('backup: completed', ['path' => $backup->path]);

            $completed = true;

            return $backup;
        } catch (Throwable $e) {
            if (File::exists($temp_zip_path)) {
                File::delete($temp_zip_path);
            }

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
            ->filter(static fn(mixed $data) => is_array($data) && $data['skipped'] !== null)
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
        /** @var int|false */
        $maxBackups = Config::get('backup.max_backups', false);

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
