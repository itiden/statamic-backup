<?php

use Illuminate\Support\Facades\File;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Exceptions\RestoreFailedException;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Facades\Restorer;

uses()->group('restorer');

it('can restore from timestamp', function () {
    $backup = Backuper::backup();

    File::cleanDirectory(config('backup.content_path'));

    expect(File::isEmptyDirectory(config('backup.content_path')))->toBeTrue();

    Restorer::restoreFromTimestamp($backup->timestamp);

    expect(File::isEmptyDirectory(config('backup.content_path')))->toBeFalse();
});

it('will not restore from unexsting path', function () {
    expect(
        fn () => Restorer::restore(new BackupDto('test', now(), '12mb', 'non_existing_path', now()->timestamp))
    )->toThrow(RestoreFailedException::class);
});
