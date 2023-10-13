<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Illuminate\Http\Request;

readonly class ChunkyTestDto
{
    public function __construct(
        public string $path,
        public string $filename,
        public int $currentChunk,
    ) {
    }

    public static function fromRequest(Request $request)
    {
        return new self(
            path: 'temp/' . $request->input('resumableIdentifier'),
            filename: $request->input('resumableFilename'),
            currentChunk: (int) $request->input('resumableChunkNumber'),
        );
    }
}
