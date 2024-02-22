<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Support\Zipper;

uses()->group('backuper');

it('can backup', function () {
    $backup = Backuper::backup();

    expect($backup)->toBeInstanceOf(BackupDto::class);

    expect(Storage::disk(config('backup.destination.disk'))
        ->exists(config('backup.destination.path') . "/{$backup->name}.zip"))->toBeTrue();
});

it('backups correct files', function () {
    $backup = Backuper::backup();

    $unzipped = config('backup.temp_path') . '/unzipped';
    Zipper::open(
        Storage::disk(config('backup.destination.disk'))
            ->path($backup->path),
        true
    )
        ->extractTo(
            $unzipped,
            config('backup.password'),
        );

    expect(File::allFiles($unzipped)[0]->getRelativePathname())
        ->toEqual('content/collections/pages/homepage.yaml');
});

it('can enforce max backups', function () {
    config()->set('backup.limit', 5);

    Backuper::backup();


    expect(app(BackupRepository::class)->all()->count())->toBeLessThanOrEqual(5);
})->repeat(10);
