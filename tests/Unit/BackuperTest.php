<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Enums\State;
use Itiden\Backup\StateManager;
use Itiden\Backup\Support\Zipper;

describe('backuper', function (): void {
    it('can backup', function (): void {
        $backup = Backuper::backup();

        expect($backup)->toBeInstanceOf(BackupDto::class);

        expect(Storage::disk(config('backup.destination.disk'))->exists(
            config('backup.destination.path') . "/{$backup->name}.zip",
        ))->toBeTrue();
    });

    it('backups correct files', function (): void {
        $backup = Backuper::backup();

        $unzipped = config('backup.temp_path') . '/unzipped';
        Zipper::read(Storage::disk(config('backup.destination.disk'))->path($backup->path))->extractTo(
            $unzipped,
            config('backup.password'),
        );

        expect(File::allFiles($unzipped)[0]->getRelativePathname())->toEqual('content/collections/pages/homepage.yaml');
    });

    it('can enforce max backups', function (): void {
        config()->set('backup.max_backups', 5);

        for ($i = 0; $i < 10; $i++) {
            // It were to fast so they all got the same timestamp
            Carbon::setTestNow(Carbon::now()->addDays($i));
            Backuper::backup();

            expect(app(BackupRepository::class)
                ->all()
                ->count())->toBeLessThanOrEqual(5);
        }
    });

    it('doesnt enforce max backups when it is disabled', function (): void {
        config()->set('backup.max_backups', false);

        for ($i = 0; $i < 10; $i++) {
            Carbon::setTestNow(Carbon::now()->addDays($i));
            Backuper::backup();

            expect(app(BackupRepository::class)
                ->all()
                ->count())->toBe($i + 1);
        }
    });

    it('cannot backup while restoring', function (): void {
        app(StateManager::class)->setState(State::RestoreInProgress);

        expect(fn() => Backuper::backup())->toThrow(Exception::class);

        app(StateManager::class)->setState(State::Idle);
    });
})->group('backuper');
