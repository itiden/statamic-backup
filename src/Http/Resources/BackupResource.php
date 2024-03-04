<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Itiden\Backup\DataTransferObjects\BackupDto
 */
class BackupResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'path' => $this->path,
            'timestamp' => $this->timestamp,
            'size' => $this->size,
        ];
    }
}
