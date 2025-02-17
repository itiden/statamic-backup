<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Itiden\Backup\DataTransferObjects\ChunkyUploadDto;
use Itiden\Backup\Support\Facades\Chunky;

use function Itiden\Backup\Tests\chunk_file;

describe('chunky', function (): void {
    it('can upload chunk', function (): void {
        $file = UploadedFile::fake()->create('test', 1000);

        $dto = new ChunkyUploadDto('dir/test', 'name', 1, 1, 1000, $file->hashName(), $file);

        $res = Chunky::put($dto);

        expect($res->getStatusCode())->toBe(201);
        expect($res->getData(true))->toHaveAttribute('message');
        expect(Chunky::path() . '/dir/test/' . $dto->filename . '.part1')->toBeFile();
    });

    it('can assemble file', function (): void {
        $chunks = chunk_file(
            __DIR__ . '/../__fixtures__/content/collections/pages/homepage.yaml',
            config('backup.temp_path') . '/chunks/',
            10,
        );

        $totalSize = File::size(__DIR__ . '/../__fixtures__/content/collections/pages/homepage.yaml');

        $dtos = $chunks->map(
            fn(string $chunk, int $index): ChunkyUploadDto => new ChunkyUploadDto(
                path: 'dir/test',
                filename: 'homepage.yaml',
                totalChunks: $chunks->count(),
                currentChunk: $index + 1,
                totalSize: $totalSize,
                identifier: basename($chunk),
                file: new UploadedFile($chunk, basename($chunk)),
            ),
        );

        $responses = $dtos->map(Chunky::put(...));

        expect($responses->every(fn(JsonResponse $res): bool => $res->getStatusCode() === 201))->toBeTrue();
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
