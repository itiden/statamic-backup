<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Itiden\Backup\Http\Requests\ChunkyUploadRequest;
use Itiden\Backup\Support\Facades\Chunky;

class UploadController extends Controller
{
    public function __invoke(ChunkyUploadRequest $request): JsonResponse
    {
        $file = $request->file('file');

        $path = 'temp/' . $request->validated('resumableIdentifier');
        $filename = $request->validated('resumableFilename');
        $totalChunks = (int) $request->validated('resumableTotalChunks');
        $currentChunk = (int)  $request->validated('resumableChunkNumber');
        $totalSize = (int) $request->validated('resumableTotalSize');

        return Chunky::put($path, $filename, $totalChunks, $currentChunk, $totalSize, $file);
    }

    public function test(Request $request): JsonResponse
    {
        return Chunky::exists(
            'temp/' . $request->input('resumableIdentifier'),
            $request->input('resumableFilename'),
            (int) $request->input('resumableChunkNumber')
        );
    }
}
