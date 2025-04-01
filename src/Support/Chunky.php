<?php

declare(strict_types=1);

namespace Itiden\Backup\Support;

use Closure;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\DataTransferObjects\ChunkyTestDto;
use Itiden\Backup\DataTransferObjects\ChunkyUploadDto;

use function Illuminate\Filesystem\join_paths;

final class Chunky
{
    private Filesystem $disk;

    public function __construct()
    {
        $this->disk = Storage::build([
            'driver' => 'local',
            'root' => config('backup.temp_path') . '/chunky',
        ]);
    }

    /**
     * Get the path to the chunky directory.
     */
    public function path(?string $path = ''): string
    {
        return $this->disk->path($path);
    }

    /**
     * Store a chunk of a file. If all chunks are uploaded, merge them into a single file.
     * @param ?Closure<string> $onCompleted Callback to run when the file is fully uploaded.
     */
    public function put(ChunkyUploadDto $dto, ?Closure $onCompleted = null): JsonResponse
    {
        if (!$this->disk->putFileAs($dto->identifier, $dto->file, $dto->filename . '.part' . $dto->currentChunk)) {
            return response()->json(['message' => 'Error saving chunk'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $chunksOnDiskSize = collect($this->disk->allFiles($dto->identifier))->reduce(
            fn(int $carry, string $item): int => $carry + $this->disk->size($item),
            0,
        );

        if ($chunksOnDiskSize < $dto->totalSize) {
            return response()->json(
                ['message' => 'uploaded ' . $dto->currentChunk . ' of ' . $dto->totalChunks],
                Response::HTTP_CREATED,
            );
        }

        $completeFile = $this->mergeChunksIntoFile($dto->identifier, $dto->filename, $dto->totalChunks);

        if ($onCompleted) {
            $onCompleted($completeFile);
        }

        return response()->json(
            ['message' => 'File successfully uploaded', 'file' => $completeFile],
            Response::HTTP_CREATED,
        );
    }

    /**
     * Merge chunks into a single file.
     */
    public function mergeChunksIntoFile(string $chunkPath, string $filename, int $totalChunks): string
    {
        $this->disk->makeDirectory('assembled');

        $assembledPath = $this->path(join_paths('assembled', $filename));

        // create the complete file
        $file = fopen($assembledPath, 'w');

        if (!$file) {
            throw new \Exception('cannot create the destination file');
        }

        // loop through the chunks and write them to the file
        for ($i = 1; $i <= $totalChunks; $i++) {
            fwrite($file, file_get_contents($this->path("{$chunkPath}/{$filename}.part{$i}")));
        }

        fclose($file);

        // delete the chunks
        $this->disk->deleteDirectory($chunkPath);

        return $assembledPath;
    }

    /**
     * Get the path to the chunk file.
     */
    public function exists(ChunkyTestDto $dto): JsonResponse
    {
        // Logic from RestoreUploadController::getChunkFilePath method goes here
        $chunk = $dto->filename . '.part' . $dto->currentChunk;

        if ($this->disk->exists($dto->identifier . '/' . $chunk)) {
            return response()->json(['message' => 'Chunk already exists'], 200);
        }

        return response()->json(['message' => 'Chunk not found'], 404);
    }
}
