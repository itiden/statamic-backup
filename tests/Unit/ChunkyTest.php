<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Itiden\Backup\DataTransferObjects\ChunkyUploadDto;
use Itiden\Backup\Support\Facades\Chunky;

describe('chunky', function () {
    it('can upload chunk', function () {
        $file = UploadedFile::fake()->create('test', 1000);

        $dto = new ChunkyUploadDto('dir/test', 'name', 1, 1, 1000, $file->hashName(), $file);

        $res = Chunky::put($dto);

        expect($res->getStatusCode())->toBe(201);
        expect($res->getData(true))->toHaveAttribute('message');
        expect(Chunky::path() . '/dir/test/' . $dto->filename . '.part1')->toBeFile();
    });

    it('can assemble file', function () {
        $chunks = chunkFile(
            __DIR__ . '/../__fixtures__/content/collections/pages/homepage.yaml',
            config('backup.temp_path') . '/chunks/',
            10,
        );

        $dtos = $chunks->map(
            fn($chunk, $index) => new ChunkyUploadDto(
                'dir/test',
                'homepage.yaml',
                $chunks->count(),
                $index + 1,
                File::size(__DIR__ . '/../__fixtures__/content/collections/pages/homepage.yaml'),
                basename($chunk),
                new UploadedFile($chunk, basename($chunk)),
            ),
        );

        $responses = $dtos->map(fn($dto) => Chunky::put($dto));

        expect($responses->every(fn($res) => $res->getStatusCode() === 201))->toBeTrue();
        expect($responses
            ->last()
            ->getData(true))->toHaveKey('file');
        expect(Chunky::path() . '/backups/homepage.yaml')->toBeFile();

        expect(File::get(Chunky::path() . '/backups/homepage.yaml'))->toBe(File::get(
            __DIR__ . '/../__fixtures__/content/collections/pages/homepage.yaml',
        ));

        File::deleteDirectory(Chunky::path());
        File::deleteDirectory(config('backup.temp_path') . '/chunks');
    });
})->group('chunky');
