<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Illuminate\Http\Request;

final readonly class ChunkyTestDto
{
    public function __construct(
        public string $identifier,
        public string $filename,
        public int $currentChunk,
    ) {
    }

    /**
     * Create a new ChunkyTestDto from a request
     */
    public static function fromRequest(Request $request): static
    {
        return new static(
            identifier: $request->input('resumableIdentifier'),
            filename: $request->input('resumableFilename'),
            currentChunk: (int) $request->input('resumableChunkNumber'),
        );
    }
}
