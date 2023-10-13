<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Itiden\Backup\DataTransferObjects\ChunkyUploadDto;
use Itiden\Backup\Support\Facades\Chunky;

uses()->group('chunky');

it('can upload chunk', function () {
    $file = UploadedFile::fake()->create('test', 1000);

    $dto = new ChunkyUploadDto(
        'dir/test',
        'name',
        1,
        1,
        1000,
        $file->hashName(),
        $file
    );

    $res = Chunky::put($dto);

    expect($res->getStatusCode())->toBe(201);
    expect($res->getData(true))->toHaveAttribute('message');

    expect(storage_path('chunks') . '/dir/test/' . $dto->filename . '.part1')->toBeFile();
});

it('can assemble file', function () {

    $chunks = chunkFile(
        __DIR__ . '/../__fixtures__/content/collections/pages/homepage.yaml',
        config('backup.temp_path') . '/chunks/',
        10
    );

    $dtos = $chunks->map(fn ($chunk, $index) => new ChunkyUploadDto(
        'dir/test',
        'homepage.yaml',
        $chunks->count(),
        $index + 1,
        File::size(__DIR__ . '/../__fixtures__/content/collections/pages/homepage.yaml'),
        basename($chunk),
        new UploadedFile($chunk, basename($chunk))
    ));

    $responses = $dtos->map(fn ($dto) => Chunky::put($dto));

    expect($responses->every(fn ($res) => $res->getStatusCode() === 201))->toBeTrue();
    expect($responses->last()->getData(true))->toHaveKey('file');
    expect(storage_path('chunks') . '/backups/homepage.yaml')->toBeFile();

    expect(File::get(storage_path('chunks') . '/backups/homepage.yaml'))->toBe(
        File::get(__DIR__ . '/../__fixtures__/content/collections/pages/homepage.yaml')
    );

    File::deleteDirectory(storage_path('chunks'));
    File::deleteDirectory(config('backup.temp_path') . '/chunks');
});

function chunkFile(string $file, string $path, int $buffer = 1024)
{
    File::ensureDirectoryExists($path);

    $fileHandle = fopen($file, 'r');
    $fileSize = filesize($file);
    $totalChunks = ceil($fileSize / $buffer);

    $chunks = collect();

    $fileName = basename($file);

    for ($i = 1; $i <= $totalChunks; $i++) {
        $chunk = fread($fileHandle, $buffer);

        $chunkPath = $path . $fileName . ".part$i";

        File::put($chunkPath, $chunk);

        $chunks->push($chunkPath);
    }
    fclose($fileHandle);

    return $chunks;
}
