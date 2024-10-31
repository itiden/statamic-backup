<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use Itiden\Backup\Abstracts\BackupPipe;

final readonly class SkippedPipeDto implements Arrayable
{
    /**
     * @param class-string<BackupPipe> $pipe
     */
    public function __construct(
        public string $pipe,
        public string $reason,
    ) {}

    public function toArray(): array
    {
        return [
            'pipe' => $this->pipe,
            'reason' => $this->reason,
        ];
    }

    public static function fromArray(array $array): self
    {
        return new self(
            pipe: $array['pipe'],
            reason: $array['reason'],
        );
    }
}
