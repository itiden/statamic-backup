<?php

declare(strict_types=1);

namespace Itiden\Backup\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Itiden\Backup\DataTransferObjects\ChunkyTestDto;
use Itiden\Backup\DataTransferObjects\ChunkyUploadDto;
use Itiden\Backup\Support\Chunky as ChunkySupport;

/**
 * @method static \Illuminate\Http\JsonResponse put(ChunkyUploadDto $dto)
 * @method static \Illuminate\Http\JsonResponse exists(ChunkyTestDto $dto)
 * @method static string path()
 *
 * @see \Itiden\Backup\Support\Chunky
 */
final class Chunky extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ChunkySupport::class;
    }
}
