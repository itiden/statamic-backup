<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Itiden\Backup\DataTransferObjects\ChunkyTestDto;
use Itiden\Backup\DataTransferObjects\ChunkyUploadDto;
use Itiden\Backup\Http\Requests\ChunkyUploadRequest;
use Itiden\Backup\Support\Facades\Chunky;

class UploadController extends Controller
{
    public function __invoke(ChunkyUploadRequest $request): JsonResponse
    {
        return Chunky::put(ChunkyUploadDto::fromRequest($request));
    }

    public function test(Request $request): JsonResponse
    {
        return Chunky::exists(
            ChunkyTestDto::fromRequest($request)
        );
    }
}
