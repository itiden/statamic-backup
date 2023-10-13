<?php

declare(strict_types=1);

namespace Itiden\Backup\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Chunky
{
    private Filesystem $disk;

    public function __construct()
    {
        $this->disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('chunks'),
        ]);;
    }

    /**
     * put a from≤ chunks.
     */
    public function put(string $path, string $filename, int $totalChunks, int $currentChunk, int $totalSize, UploadedFile $file): JsonResponse
    {
        if (!$this->disk->putFileAs($path, $file, $filename . '.part' . $currentChunk)) {
            return response()->json(['message' => 'Error saving chunk'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $chunksOnDiskSize = collect($this->disk->allFiles($path))->reduce(fn ($carry, $item) => $carry + $this->disk->size($item), 0);

        if ($chunksOnDiskSize < $totalSize) {
            return response()->json(['message' => 'uploaded ' . $currentChunk . ' of ' . $totalChunks], Response::HTTP_CREATED);
        }

        if ($complete_file = $this->mergeChunksIntoFile($path, $filename, $totalChunks)) {
            return response()->json(['message' => 'File successfully uploaded', 'file' => $complete_file], Response::HTTP_CREATED);
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
    public function exists(string $path, string $filename, int $currentChunk): JsonResponse
    {
        // Logic from RestoreUploadController::getChunkFilePath method goes here
        $chunk_file = $filename . '.part' . $currentChunk;

        if ($this->disk->exists($path . '/' . $chunk_file)) {
            return response()->json(['message' => 'Chunk already exists'], 200);
        }

        return response()->json(['message' => 'Chunk not found'], 404);
    }
}
