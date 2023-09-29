<?php

use Illuminate\Support\Facades\File;
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
