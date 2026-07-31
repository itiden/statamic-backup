<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Carbon\CarbonImmutable;
use Statamic\Contracts\Auth\User;
use Statamic\Facades\User as UserFacade;

final readonly class UserActionDto
{
    public function __construct(
        public string $userId,
        /** A human readable string */
        public string $timestamp,
    ) {}

    public function getUser(): ?User
    {
        return UserFacade::find($this->userId);
    }

    public function getTimestamp(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->timestamp);
    }

    /** @return array{user_id: string, timestamp: string}*/
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'timestamp' => $this->timestamp,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(userId: $data['user_id'], timestamp: $data['timestamp']);
    }
}
