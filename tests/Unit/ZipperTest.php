<?php

use Illuminate\Support\Facades\File;
use Itiden\Backup\Support\Zipper;

uses()->group('zipper');

it('can create instance', function () {
    $zip = new Zipper(storage_path('test.zip'));

    expect($zip)->toBeInstanceOf(Zipper::class);
});

it('can zip file', function () {
    $target = storage_path('test.zip');

    Zipper::make($target)
        ->addFromString('test.txt', 'test')
        ->close();

    expect(file_exists($target))->toBeTrue();

    expect(File::mimeType($target))->toBe('application/zip');
});

it('can zip directory', function () {
    $path = storage_path('test.zip');

    Zipper::make($path)
        ->addDirectory(config('backup.content_path'), 'example');

    expect($path)->toBeString();
    expect(file_exists($path))->toBeTrue();
});

it('can unzip file', function () {
    $target = storage_path('test.zip');

    Zipper::make($target)
        ->addFromString('test.txt', 'test')
        ->close();

    $unzip = storage_path('unzip');

    Zipper::open($target)
        ->unzipTo($unzip)
        ->close();

    expect(file_exists($unzip))->toBeTrue();
});

it('can unzip file to directory', function () {
    $target = storage_path('test.zip');

    Zipper::make($target)->addFromString('test.txt', 'test');

    $unzip = storage_path('test');
    Zipper::open($target)->unzipTo($unzip)->close();

    expect(file_exists($unzip))->toBeTrue();
    expect(file_exists(storage_path('test') . '/test.txt'))->toBeTrue();
});

it('can unzip directory', function () {
    $files = collect(File::allFiles(config('backup.content_path')))->map(function (SplFileInfo $file) {
        return $file->getPathname();
    });

    $target = storage_path('test.zip');

    Zipper::make($target)
        ->addDirectory(config('backup.content_path'), 'example');

    $unzip = storage_path('unzipdir');
    Zipper::open($target)->unzipTo($unzip)->close();

    expect(file_exists($unzip))->toBeTrue();
    expect(file_exists(storage_path('unzipdir') . '/example'))->toBeTrue();

    $files->each(function ($file) {
        expect(file_exists($file))->toBeTrue();
    });
});
