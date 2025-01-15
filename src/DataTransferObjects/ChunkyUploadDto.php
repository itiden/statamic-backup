<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final readonly class ChunkyUploadDto
{
    public function __construct(
        public string $path,
        public string $filename,
        public int $totalChunks,
        public int $currentChunk,
        public int $totalSize,
        public string $identifier,
        public UploadedFile $file,
    ) {
    }

    /**
     * Create a new ChunkyUploadDto from a request
     */
    public static function fromRequest(Request $request)
    {
        return new self(
            path: 'temp/' . $request->input('resumableIdentifier'),
            filename: $request->input('resumableFilename'),
            totalChunks: (int) $request->input('resumableTotalChunks'),
            currentChunk: (int) $request->input('resumableChunkNumber'),
            totalSize: (int) $request->input('resumableTotalSize'),
            identifier: $request->input('resumableIdentifier'),
            file: $request->file('file'),
        );
    }
}
