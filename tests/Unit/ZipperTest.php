<?php

use Illuminate\Support\Facades\File;
use Itiden\Backup\Support\Zipper;

it('can return ziparchive', function () {
    $zip = Zipper::zip(storage_path('test.zip'), function ($zip) {
        $zip->addFromString('test.txt', 'test');
    });

    expect($zip)->toBeInstanceOf(ZipArchive::class);
});

it('can zip file', function () {
    $target = storage_path('test.zip');

    $zip = Zipper::zip($target, function ($zip) {
        $zip->addFromString('test.txt', 'test');
    });

    expect(file_exists($target))->toBeTrue();

    expect(File::mimeType($target))->toBe('application/zip');
});

it('can zip directory', function () {
    $path = storage_path('test.zip');

    Zipper::zip($path, function ($zip) {
        Zipper::zipDir(__DIR__ . '/../example', $zip, 'example');
    });

    expect($path)->toBeString();
    expect(file_exists($path))->toBeTrue();
});

it('can unzip file', function () {
    $target = storage_path('test.zip');

    Zipper::zip($target, function ($zip) {
        $zip->addFromString('test.txt', 'test');
    });

    $unzip = Zipper::unzip($target, storage_path('unzipped'));

    expect($unzip)->toBeString();
    expect(file_exists($unzip))->toBeTrue();
});

it('can unzip file to directory', function () {
    $target = storage_path('test.zip');

    Zipper::zip($target, function ($zip) {
        $zip->addFromString('test.txt', 'test');
    });

    $unzip = Zipper::unzip($target, storage_path('test'));

    expect($unzip)->toBeString();
    expect(file_exists($unzip))->toBeTrue();
    expect(file_exists(storage_path('test') . '/test.txt'))->toBeTrue();
});

it('can unzip directory', function () {
    $files = collect(File::allFiles(__DIR__ . '/../example'))->map(function (SplFileInfo $file) {
        return $file->getPathname();
    });

    $target = storage_path('test.zip');

    Zipper::zip($target, function ($zip) {
        Zipper::zipDir(__DIR__ . '/../example', $zip, 'example');
    });

    $unzip = Zipper::unzip($target, storage_path('unzipdir'));

    expect($unzip)->toBeString();
    expect(file_exists($unzip))->toBeTrue();
    expect(file_exists(storage_path('unzipdir') . '/example'))->toBeTrue();

    $files->each(function ($file) {
        expect(file_exists($file))->toBeTrue();
    });
});
