<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Exceptions\RestoreFailed;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Facades\Restorer;

describe('restorer', function (): void {
    it('can restore from timestamp', function (): void {
        $backup = Backuper::backup();

        File::cleanDirectory(config('backup.content_path'));

        expect(File::isEmptyDirectory(config('backup.content_path')))->toBeTrue();

        Restorer::restoreFromTimestamp($backup->timestamp);

        expect(File::isEmptyDirectory(config('backup.content_path')))->toBeFalse();
    });

    it('throws an exception if the backup path does not exist', function (): void {
        Restorer::restore(
            new BackupDto(
                name: 'test',
                created_at: now(),
                size: '0',
                path: 'test/path',
                timestamp: (string) now()->timestamp,
            ),
        );
    })->throws(RestoreFailed::class);
})->group('restorer');
