<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Itiden\Backup\DataTransferObjects\SkippedPipeDto;

/**
 * @mixin \Itiden\Backup\Models\Metadata
 */
final class MetadataResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'created_by' => $this->getCreatedBy(),
            'downloads' => $this->getDownloads(),
            'restores' => $this->getRestores(),
            'skipped_pipes' => $this->getSkippedPipes()->map(fn (SkippedPipeDto $pipe) => [
                'pipe' => $pipe->pipe::getKey(),
                'reason' => $pipe->reason,
            ]),
        ];
    }
}
