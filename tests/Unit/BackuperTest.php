<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\BackupNameResolver;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Enums\State;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\StateManager;
use Itiden\Backup\Support\Zipper;
use Statamic\Facades\Stache;
use Symfony\Component\Finder\SplFileInfo;

use function Itiden\Backup\Tests\user;

describe('backuper', function (): void {
    it('can backup', function (): void {
        Carbon::setTestNow(Carbon::now());

        $backup = Backuper::backup();

        $filename = app(BackupNameResolver::class)->generateFilename(Carbon::now()->toImmutable(), $backup->id);

        expect($backup)->toBeInstanceOf(BackupDto::class);

        expect(Storage::disk(config('backup.destination.disk'))->exists(
            str(config('backup.destination.path') . "/{$filename}")->finish('.zip'),
        ))->toBeTrue();

        expect(pathinfo(Storage::disk(config('backup.destination.disk'))->path($backup->path), PATHINFO_EXTENSION))
            ->toBe('zip');

        $zipper = Zipper::read(Storage::disk(config('backup.destination.disk'))->path($backup->path));

        $meta = $zipper->getMeta();
        expect($meta)->toHaveKey('is_backup', 'true');
        expect($meta)->toHaveKey('version', '1');

        $zipper->close();
    });

    it('backups correct files', function (): void {
        expect(File::allFiles(Stache::store('entries')->directory()))->toHaveCount(2); // 1 entry, 1 collection
        expect(File::allFiles(Stache::store('form-submissions')->directory()))->toHaveCount(1);
        user();

        $backup = Backuper::backup();

        $unzipped = config('backup.temp_path') . '/unzipped';
        Zipper::read(Storage::disk(config('backup.destination.disk'))->path($backup->path))
            ->extractTo($unzipped, config('backup.password'))
            ->close();

        $paths = collect(File::allFiles($unzipped))
            ->map(fn(SplFileInfo $file) => $file->getRelativePathname())
            ->toArray();

        expect($paths)
            ->toEqualCanonicalizing([
                // since the default collection store and entries store have the same directory, we will get duplicates.
                'stache-content::collections/pages.yaml',
                'stache-content::collections/pages/homepage.md',
                'stache-content::entries/pages.yaml',
                'stache-content::entries/pages/homepage.md',
                'stache-content::form-submissions/1743066599.5568.yaml',
                'users/test@example.com.yaml',
            ]);

        File::deleteDirectory($unzipped);
    });

    it('backups correct files and only include stache stores in config', function (): void {
        expect(File::allFiles(Stache::store('entries')->directory()))->toHaveCount(2); // 1 entry, 1 collection
        expect(File::allFiles(Stache::store('form-submissions')->directory()))->toHaveCount(1);
        user();

        config()->set('backup.stache_stores', [
            'form-submissions',
        ]);

        $backup = Backuper::backup();

        $unzipped = config('backup.temp_path') . '/unzipped';

        Zipper::read(Storage::disk(config('backup.destination.disk'))->path($backup->path))
            ->extractTo($unzipped, config('backup.password'))
            ->close();

        $paths = collect(File::allFiles($unzipped))
            ->map(fn(SplFileInfo $file) => $file->getRelativePathname())
            ->toArray();

        expect($paths)
            ->toEqualCanonicalizing([
                'stache-content::form-submissions/1743066599.5568.yaml',
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

            expect(app(BackupRepository::class)->all()->count())->toBeLessThanOrEqual(5);
        }
    });

    it('doesnt enforce max backups when it is disabled', function (): void {
        config()->set('backup.max_backups', false);

        for ($i = 0; $i < 10; $i++) {
            Carbon::setTestNow(Carbon::now()->addDays($i));
            Backuper::backup();

            expect(app(BackupRepository::class)->all()->count())->toBe($i + 1);
        }
    });

    it('cannot backup while restoring', function (): void {
        app(StateManager::class)->setState(State::RestoreInProgress);

        expect(fn() => Backuper::backup())->toThrow(Exception::class);

        app(StateManager::class)->setState(State::Idle);
    });
})->group('backuper');
