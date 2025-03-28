<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\ChunkyTestDto;
use Itiden\Backup\DataTransferObjects\ChunkyUploadDto;
use Itiden\Backup\Http\Requests\ChunkyUploadRequest;
use Itiden\Backup\Support\Facades\Chunky;

final readonly class UploadController
{
    public function __invoke(ChunkyUploadRequest $request, BackupRepository $repo): JsonResponse
    {
        return Chunky::put(ChunkyUploadDto::fromRequest($request), onCompleted: function (string $completeFile) use (
            $repo,
        ): void {
            $repo->add($completeFile);
        });
    }

    public function test(Request $request): JsonResponse
    {
        return Chunky::exists(ChunkyTestDto::fromRequest($request));
    }
}
