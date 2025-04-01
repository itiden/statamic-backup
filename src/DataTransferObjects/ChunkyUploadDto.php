<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final readonly class ChunkyUploadDto
{
    // @mago-ignore maintainability/excessive-parameter-list
    public function __construct(
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
    public static function fromRequest(Request $request): static
    {
        return new static(
            filename: $request->input('resumableFilename'),
            totalChunks: (int) $request->input('resumableTotalChunks'),
            currentChunk: (int) $request->input('resumableChunkNumber'),
            totalSize: (int) $request->input('resumableTotalSize'),
            identifier: $request->input('resumableIdentifier'),
            file: $request->file('file'),
        );
    }
}
