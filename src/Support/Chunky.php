<?php

declare(strict_types=1);

namespace Itiden\Backup\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\DataTransferObjects\ChunkyTestDto;
use Itiden\Backup\DataTransferObjects\ChunkyUploadDto;

class Chunky
{
    private Filesystem $disk;

    public function __construct()
    {
        $this->disk = Storage::build([
            'driver' => 'local',
            'root' => config('backup.temp_path'), '/chunky',
        ]);
    }

    /**
     * Get the path to the chunky directory.
     */
    public function path(): string
    {
        return $this->disk->path('');
    }

    /**
     * put a from≤ chunks.
     */
    public function put(ChunkyUploadDto $dto): JsonResponse
    {
        if (!$this->disk->putFileAs($dto->path, $dto->file, $dto->filename . '.part' . $dto->currentChunk)) {
            return response()->json(['message' => 'Error saving chunk'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $chunksOnDiskSize = collect($this->disk->allFiles($dto->path))->reduce(fn ($carry, $item) => $carry + $this->disk->size($item), 0);

        if ($chunksOnDiskSize < $dto->totalSize) {
            return response()->json(['message' => 'uploaded ' . $dto->currentChunk . ' of ' . $dto->totalChunks], Response::HTTP_CREATED);
        }

        if ($completeFile = $this->mergeChunksIntoFile($dto->path, $dto->filename, $dto->totalChunks)) {
            return response()->json(['message' => 'File successfully uploaded', 'file' => $completeFile], Response::HTTP_CREATED);
        }

        return response()->json(['message' => 'Error restoring file'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Merge chunks into a single file.
     */
    public function mergeChunksIntoFile(string $path, string $filename, int $totalChunks): string
    {
        $fullPath = $this->disk->path($path . '/' . $filename);

        // create the complete file
        if (($file = fopen($fullPath, 'w')) !== false) {
            for ($i = 1; $i <= $totalChunks; $i++) {
                fwrite($file, file_get_contents($fullPath . '.part' . $i));
                info('writing chunk ' . $i);
            }
            fclose($file);
        } else {
            throw new \Exception('cannot create the destination file');
        }

        // move the file to the backups folder
        $this->disk->move($path . '/' . $filename, 'backups/' . $filename);

        // delete the chunks
        $this->disk->deleteDirectory($path);

        return $this->disk->path('backups/' . $filename);
    }

    /**
     * Get the path to the chunk file.
     */
    public function exists(ChunkyTestDto $dto): JsonResponse
    {
        // Logic from RestoreUploadController::getChunkFilePath method goes here
        $chunk = $dto->filename . '.part' . $dto->currentChunk;

        if ($this->disk->exists($dto->path . '/' . $chunk)) {
            return response()->json(['message' => 'Chunk already exists'], 200);
        }

        return response()->json(['message' => 'Chunk not found'], 404);
    }
}
