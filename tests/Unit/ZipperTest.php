<?php

use Illuminate\Support\Facades\File;
use Itiden\Backup\Support\Zipper;

it('can zip file', function () {
    $zip = Zipper::zip(function ($zip) {
        $zip->addFromString('test.txt', 'test');
    });

    expect($zip)->toBeString();
    expect(file_exists($zip))->toBeTrue();

    expect(File::mimeType($zip))->toBe('application/zip');
});

it('can zip directory', function () {
    $zip = Zipper::zip(function ($zip) {
        Zipper::zipDir(__DIR__ . '/../example', $zip, 'example');
    });

    expect($zip)->toBeString();
    expect(file_exists($zip))->toBeTrue();
});

it('can unzip file', function () {
    $zip = Zipper::zip(function ($zip) {
        $zip->addFromString('test.txt', 'test');
    });

    $unzip = Zipper::unzip($zip);

    expect($unzip)->toBeString();
    expect(file_exists($unzip))->toBeTrue();
});

it('can unzip file to directory', function () {
    $zip = Zipper::zip(function ($zip) {
        $zip->addFromString('test.txt', 'test');
    });

    $unzip = Zipper::unzip($zip, storage_path('test'));

    expect($unzip)->toBeString();
    expect(file_exists($unzip))->toBeTrue();
    expect(file_exists(storage_path('test') . '/test.txt'))->toBeTrue();
});

it('can unzip directory', function () {
    $files = collect(File::allFiles(__DIR__ . '/../example'))->map(function (SplFileInfo $file) {
        return $file->getPathname();
    });

    $zip = Zipper::zip(function ($zip) {
        Zipper::zipDir(__DIR__ . '/../example', $zip, 'example');
    });

    $unzip = Zipper::unzip($zip, storage_path('unzipdir'));

    expect($unzip)->toBeString();
    expect(file_exists($unzip))->toBeTrue();
    expect(file_exists(storage_path('unzipdir') . '/example'))->toBeTrue();

    $files->each(function ($file) {
        expect(file_exists($file))->toBeTrue();
    });
});
