<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Itiden\Backup\Abstracts\BackupPipe;

final readonly class SkippedPipeDto
{
    public function __construct(
        /** @var class-string<BackupPipe> */
        public string $pipe,
        public string $reason,
    ) {}

    /** @return array{pipe: class-string<BackupPipe>, reason: string}*/
    public function toArray(): array
    {
        return [
            'pipe' => $this->pipe,
            'reason' => $this->reason,
        ];
    }

    /**
     * @param array{pipe: class-string<BackupPipe>, reason: string} $array
     */
    public static function fromArray(array $array): SkippedPipeDto
    {
        return new static(pipe: $array['pipe'], reason: $array['reason']);
    }
}
