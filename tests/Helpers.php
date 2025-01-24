<?php

declare(strict_types=1);

namespace Itiden\Backup\Tests {
    use Illuminate\Support\Facades\File;

    /**
     * Split a file into chunks
     */
    function chunk_file(string $file, string $path, int $buffer = 1024)
    {
        File::ensureDirectoryExists($path);

        $fileHandle = fopen($file, 'r');
        $fileSize = File::size($file);
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
}
