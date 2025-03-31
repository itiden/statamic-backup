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

        $dto = new ChunkyUploadDto('name', 1, 1, 1000, $file->hashName(), $file);

        $res = Chunky::put($dto);

        expect($res->getStatusCode())->toBe(201);
        expect($res->getData(true))->toHaveAttribute('message');
        expect(Chunky::path() . '/' . $file->hashName() . '/' . $dto->filename . '.part1')->toBeFile();
    });

    it('can assemble file', function (): void {
        $chunks = chunk_file(
            __DIR__ . '/../__fixtures__/content/collections/pages/homepage.md',
            config('backup.temp_path') . '/chunks/',
            10,
        );

        $totalSize = File::size(__DIR__ . '/../__fixtures__/content/collections/pages/homepage.md');

        $dtos = $chunks->map(
            fn(string $chunk, int $index): ChunkyUploadDto => new ChunkyUploadDto(
                filename: 'homepage.md',
                totalChunks: $chunks->count(),
                currentChunk: $index + 1,
                totalSize: $totalSize,
                identifier: 'homepage-and-some-hash',
                file: new UploadedFile($chunk, basename($chunk)),
            ),
        );

        $uploadedFile = null;

        $responses = $dtos->map(function (ChunkyUploadDto $r) use (&$uploadedFile): JsonResponse {
            return Chunky::put($r, onCompleted: function (string $file) use (&$uploadedFile): void {
                $uploadedFile = $file;
            });
        });

        expect($responses->every(fn(JsonResponse $res): bool => $res->getStatusCode() === 201))->toBeTrue();
        expect($responses
            ->last()
            ->getData(true))->toHaveKey('file');

        expect(Chunky::path('assembled/homepage.md'))->toBeFile();

        expect(File::get(Chunky::path('/assembled/homepage.md')))->toBe(File::get(
            __DIR__ . '/../__fixtures__/content/collections/pages/homepage.md',
        ));

        expect($uploadedFile)->toEqual(Chunky::path('/assembled/homepage.md'));

        File::deleteDirectory(Chunky::path());
        File::deleteDirectory(config('backup.temp_path') . '/chunks');
    });
})->group('chunky');
