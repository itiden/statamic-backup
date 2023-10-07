<?php

declare(strict_types=1);

namespace Itiden\Backup\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Itiden\Backup\Support\Chunky as ChunkySupport;

/**
 * @method static \Illuminate\Http\JsonResponse put(string $path, string $filename, int $totalChunks, int $currentChunk, int $totalSize, \Illuminate\Http\UploadedFile $file)
 * @method static \Illuminate\Http\JsonResponse exists(string $path, string $filename, int $currentChunk)
 *
 * @see \Itiden\Backup\Support\Chunky
 */
class Chunky extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ChunkySupport::class;
    }
}
