<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Exceptions\RestoreFailed;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Facades\Restorer;
use Itiden\Backup\Enums\State;
use Itiden\Backup\StateManager;
use Statamic\Facades\Stache;

use function Itiden\Backup\Tests\fixtures_path;
use function Itiden\Backup\Tests\user;

describe('restorer', function (): void {
    it('can restore from id', function (): void {
        $backup = Backuper::backup();

        File::cleanDirectory(fixtures_path('content/collections'));

        expect(File::isEmptyDirectory(fixtures_path('content/collections')))->toBeTrue();

        Restorer::restoreFromId($backup->id);

        expect(File::isEmptyDirectory(fixtures_path('content/collections')))->toBeFalse();
    });

    it('restores correct files', function (): void {
        user();
        expect(File::allFiles(Stache::store('entries')->directory()))->toHaveCount(2); // 1 entry, 1 collection
        expect(File::allFiles(Stache::store('form-submissions')->directory()))->toHaveCount(1);
        expect(File::allFiles(Stache::store('users')->directory()))->toHaveCount(1);

        // config()->set('backup.stache_stores', [
        //     'form-submissions',
        // ]);

        $backup = Backuper::backup();

        File::cleanDirectory(fixtures_path('content')); // Simulate the stache directories doesnt exist anymore by removing the their parent directory
        File::cleanDirectory(Stache::store('users')->directory());

        expect(file_exists(Stache::store('entries')->directory()))->toBeFalse();
        expect(file_exists(Stache::store('form-submissions')->directory()))->toBeFalse();
        expect(File::allFiles(Stache::store('users')->directory()))->toHaveCount(0);

        Restorer::restore($backup);

        expect(File::allFiles(Stache::store('entries')->directory()))->toHaveCount(2); // 1 entry, 1 collection
        expect(File::allFiles(Stache::store('form-submissions')->directory()))->toHaveCount(1);
        expect(File::allFiles(Stache::store('users')->directory()))->toHaveCount(1);
    });

    it('throws an exception if the backup path does not exist', function (): void {
        Restorer::restore(
            new BackupDto(
                name: 'test',
                created_at: now()->toImmutable(),
                size: '0',
                path: 'test/path',
                id: (string) random_bytes(10),
            ),
        );
    })->throws(RestoreFailed::class);

    it('cannot restore while a backup is in progress', function (): void {
        $backup = Backuper::backup();

        app(StateManager::class)->setState(State::BackupInProgress);

        expect(fn() => Restorer::restore($backup))->toThrow(Exception::class);

        app(StateManager::class)->setState(State::Idle);
    });
})->group('restorer');
