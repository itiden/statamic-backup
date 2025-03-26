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
use Statamic\Facades\Stache;

describe('backuper', function (): void {
    it('can backup', function (): void {
        $this->withoutExceptionHandling();
        $backup = Backuper::backup();

        expect($backup)->toBeInstanceOf(BackupDto::class);

        expect(Storage::disk(config('backup.destination.disk'))->exists(
            config('backup.destination.path') . "/{$backup->name}.zip",
        ))->toBeTrue();
    });

    it('backups correct files', function (): void {
        expect(File::allFiles(Stache::store('entries')->directory()))->toHaveCount(2); // 1 entry, 1 collection

        $backup = Backuper::backup();

        $unzipped = config('backup.temp_path') . '/unzipped';
        Zipper::read(Storage::disk(config('backup.destination.disk'))->path($backup->path))
            ->extractTo($unzipped, config('backup.password'))
            ->close();

        $paths = collect(File::allFiles($unzipped))->map(fn($file) => $file->getRelativePathname())->toArray();

        expect($paths)->toEqualCanonicalizing([
            'stache-content::collections/pages.yaml',
            'stache-content::collections/pages/homepage.md',
            'stache-content::entries/pages.yaml',
            'stache-content::entries/pages/homepage.md',
            'users/test@example.com.yaml',
        ]);

        File::deleteDirectory($unzipped);
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
