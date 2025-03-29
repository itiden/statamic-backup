<?php

declare(strict_types=1);

namespace Itiden\Backup\DataTransferObjects;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Auth\User;
use Statamic\Facades\User as UserFacade;

final readonly class UserActionDto implements Arrayable
{
    public function __construct(
        public string $userId,
        public string $timestamp,
    ) {}

    public function getUser(): ?User
    {
        return UserFacade::find($this->userId);
    }

    public function getTimestamp(): CarbonImmutable
    {
        return CarbonImmutable::createFromDate($this->timestamp);
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'timestamp' => $this->timestamp,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            userId: $data['user_id'],
            timestamp: $data['timestamp'],
        );
    }
}
