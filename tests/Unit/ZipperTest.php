<?php

use Illuminate\Support\Facades\File;
use Itiden\Backup\Support\Zipper;

describe('zipper', function () {

    it('can create instance', function () {
        $zip = new Zipper(storage_path('test.zip'));

        expect($zip)->toBeInstanceOf(Zipper::class);
    });

    it('can get the zipArchive instance', function () {
        $zip = new Zipper(storage_path('test.zip'));

        expect($zip->getArchive())->toBeInstanceOf(ZipArchive::class);
    });

    it('can zip file from string', function () {
        $target = storage_path('test.zip');

        Zipper::open($target)
            ->addFromString('test.txt', 'test')
            ->close();

        expect(file_exists($target))->toBeTrue();

        expect(File::mimeType($target))->toBe('application/zip');
    });

    it('can zip file from path', function () {
        $target = storage_path('test.zip');

        Zipper::open($target)
            ->addFile(__FILE__)
            ->close();

        expect(file_exists($target))->toBeTrue();

        expect(File::mimeType($target))->toBe('application/zip');
    });

    it('can zip directory', function () {
        $path = storage_path('test.zip');

        Zipper::open($path)
            ->addDirectory(config('backup.content_path'), 'example');

        expect($path)->toBeString();
        expect(file_exists($path))->toBeTrue();
    });

    it('can unzip file', function () {
        $target = storage_path('test.zip');

        Zipper::open($target)
            ->addFromString('test.txt', 'test')
            ->close();

        $unzip = storage_path('unzip');

        Zipper::open($target, true)
            ->extractTo($unzip)
            ->close();

        expect(file_exists($unzip))->toBeTrue();
    });

    it('can unzip file to directory', function () {
        $target = storage_path('test.zip');

        Zipper::open($target)->addFromString('test.txt', 'test');

        $unzip = storage_path('test');
        Zipper::open($target, true)->extractTo($unzip)->close();

        expect(file_exists($unzip))->toBeTrue();
        expect(file_exists(storage_path('test') . '/test.txt'))->toBeTrue();
    });

    it('can unzip directory', function () {
        $files = collect(File::allFiles(config('backup.content_path')))->map(function (SplFileInfo $file) {
            return $file->getPathname();
        });

        $target = storage_path('test.zip');

        Zipper::open($target)
            ->addDirectory(config('backup.content_path'), 'example');

        $unzip = storage_path('unzipdir');
        Zipper::open($target, true)->extractTo($unzip)->close();

        expect(file_exists($unzip))->toBeTrue();
        expect(file_exists(storage_path('unzipdir') . '/example'))->toBeTrue();

        $files->each(function ($file) {
            expect(file_exists($file))->toBeTrue();
        });
    });

    it('can encrypt when zipping', function () {
        $target = storage_path('test.zip');
        $password = 'password';

        Zipper::open($target)
            ->addFromString('test.txt', 'test')
            ->encrypt($password)
            ->close();

        expect(file_exists($target))->toBeTrue();
        expect(File::mimeType($target))->toBe('application/zip');

        $unzip = storage_path('unzip');

        Zipper::open($target, true)
            ->extractTo($unzip, $password)
            ->close();

        expect(File::allFiles($unzip)[0]->getRelativePathname())->toBe("test.txt");
        expect(File::get($unzip . '/test.txt'))->toBe('test');
    });

    it('can write meta to zip', function () {
        $target = storage_path('test.zip');

        Zipper::open($target)
            ->addFromString('test.txt', 'test')
            ->addMeta('test', 'test')
            ->close();

        $zip = Zipper::open($target, true);

        expect($zip->getMeta())->toHaveKey('test');
        expect($zip->getMeta())->get('test')->toBe('test');

        $zip->close();
    });
})->group('zipper');
