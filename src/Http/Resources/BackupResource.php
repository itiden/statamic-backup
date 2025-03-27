<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Itiden\Backup\DataTransferObjects\BackupDto
 */
final class BackupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'path' => $this->path,
            'timestamp' => $this->id,
            'size' => $this->size,
            'metadata' => new MetadataResource(resource: $this->getMetadata()),
        ];
    }
}
