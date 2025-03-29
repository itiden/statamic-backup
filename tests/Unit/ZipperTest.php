<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Itiden\Backup\Support\Zipper;

use function Itiden\Backup\Tests\fixtures_path;

describe('zipper', function (): void {
    it('can create instance', function (): void {
        $zip = new Zipper(storage_path('test.zip'));

        expect($zip)->toBeInstanceOf(Zipper::class);
    });

    it('can get the zipArchive instance', function (): void {
        $zip = new Zipper(storage_path('test.zip'));

        expect($zip->getArchive())->toBeInstanceOf(ZipArchive::class);
    });

    it('can zip file from string', function (): void {
        $target = storage_path('test.zip');

        Zipper::write($target)->addFromString('test.txt', 'test')->close();

        expect(file_exists($target))->toBeTrue();

        expect(File::mimeType($target))->toBe('application/zip');
    });

    it('can zip file from path', function (): void {
        $target = storage_path('test.zip');

        Zipper::write($target)->addFile(__FILE__)->close();

        expect(file_exists($target))->toBeTrue();

        expect(File::mimeType($target))->toBe('application/zip');
    });

    it('can zip directory', function (): void {
        $path = storage_path('test.zip');

        Zipper::write($path)->addDirectory(fixtures_path('content/collections'), 'example');

        expect($path)->toBeString();
        expect(file_exists($path))->toBeTrue();
    });

    it('can unzip file', function (): void {
        $target = storage_path('test.zip');

        Zipper::write($target)->addFromString('test.txt', 'test')->close();

        $unzip = storage_path('unzip');

        Zipper::read($target)->extractTo($unzip)->close();

        expect(file_exists($unzip))->toBeTrue();
    });

    it('can unzip file to directory', function (): void {
        $target = storage_path('test.zip');

        Zipper::write($target)->addFromString('test.txt', 'test')->close();

        $unzip = storage_path('test');
        Zipper::read($target)->extractTo($unzip)->close();

        expect(file_exists($unzip))->toBeTrue();
        expect(file_exists(storage_path('test') . '/test.txt'))->toBeTrue();
    });

    it('can unzip directory', function (): void {
        $files = collect(File::allFiles(fixtures_path('content/collections')))
            ->map(fn(SplFileInfo $file): string => $file->getPathname());

        $target = storage_path('test.zip');

        Zipper::write($target)->addDirectory(fixtures_path('content/collections'), 'example')->close();

        $unzip = storage_path('unzipdir');
        Zipper::read($target)->extractTo($unzip)->close();

        expect(file_exists($unzip))->toBeTrue();
        expect(file_exists(storage_path('unzipdir') . '/example'))->toBeTrue();

        $files->each(function (string $file): void {
            expect(file_exists($file))->toBeTrue();
        });
    });

    it('can encrypt when zipping', function (): void {
        $target = storage_path('test.zip');
        // @mago-expect security/no-literal-password
        $password = 'password';

        Zipper::write($target)
            ->addFromString('test.txt', 'test')
            ->encrypt($password)
            ->close();

        expect(file_exists($target))->toBeTrue();
        expect(File::mimeType($target))->toBe('application/zip');

        $unzip = storage_path('unzip');

        Zipper::read($target)->extractTo($unzip, $password)->close();

        expect(File::allFiles($unzip)[0]->getRelativePathname())->toBe('test.txt');
        expect(File::get($unzip . '/test.txt'))->toBe('test');
    });

    it('can write meta to zip', function (): void {
        $target = storage_path('test.zip');

        Zipper::write($target)
            ->addFromString('test.txt', 'test')
            ->addMeta('test', 'test')
            ->close();

        $zip = Zipper::read($target);

        expect($zip->getMeta())->toHaveKey('test');
        expect($zip->getMeta())->get('test')->toBe('test');

        $zip->close();
    });
})->group('zipper');
