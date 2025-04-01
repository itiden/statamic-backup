<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Itiden\Backup\DataTransferObjects\ChunkyUploadDto;
use Itiden\Backup\Support\Chunky;

use function Itiden\Backup\Tests\chunk_file;

describe('chunky', function (): void {
    it('can upload chunk', function (): void {
        $file = UploadedFile::fake()->create('test', 1000);

        $dto = new ChunkyUploadDto('name', 1, 1, 1000, $file->hashName(), $file);

        $res = app(Chunky::class)->put($dto);

        expect($res->getStatusCode())->toBe(201);
        expect($res->getData(true))->toHaveAttribute('message');
        expect(app(Chunky::class)->path() . '/' . $file->hashName() . '/' . $dto->filename . '.part1')->toBeFile();
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
            return app(Chunky::class)->put($r, onCompleted: function (string $file) use (&$uploadedFile): void {
                $uploadedFile = $file;
            });
        });

        expect($responses->every(fn(JsonResponse $res): bool => $res->getStatusCode() === 201))->toBeTrue();
        expect($responses
            ->last()
            ->getData(true))->toHaveKey('file');

        expect(app(Chunky::class)->path('assembled/homepage.md'))->toBeFile();

        expect(File::get(app(Chunky::class)->path('/assembled/homepage.md')))->toBe(File::get(
            __DIR__ . '/../__fixtures__/content/collections/pages/homepage.md',
        ));

        expect($uploadedFile)->toEqual(app(Chunky::class)->path('/assembled/homepage.md'));

        File::deleteDirectory(app(Chunky::class)->path());
        File::deleteDirectory(config('backup.temp_path') . '/chunks');
    });
})->group('chunky');
