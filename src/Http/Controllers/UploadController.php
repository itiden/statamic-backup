<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Itiden\Backup\Backuper;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\ChunkyTestDto;
use Itiden\Backup\DataTransferObjects\ChunkyUploadDto;
use Itiden\Backup\Http\Requests\ChunkyUploadRequest;
use Itiden\Backup\Support\Chunky;

final readonly class UploadController
{
    public function __invoke(
        ChunkyUploadRequest $request,
        Chunky $chunky,
        BackupRepository $repo,
        Backuper $backuper,
    ): JsonResponse {
        return $chunky->put(ChunkyUploadDto::fromRequest($request), onCompleted: function (string $completeFile) use (
            $repo,
            $backuper,
        ): void {
            $backup = $repo->add($completeFile);

            $backuper->addMetaFromZipToBackupMeta($completeFile, $backup);

            $backuper->enforceMaxBackups();
        });
    }

    public function test(Request $request, Chunky $chunky): JsonResponse
    {
        return $chunky->exists(ChunkyTestDto::fromRequest($request));
    }
}
