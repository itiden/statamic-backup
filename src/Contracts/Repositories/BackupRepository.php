<?php

declare(strict_types=1);

namespace Itiden\Backup\Contracts\Repositories;

use Illuminate\Support\Collection;
use Itiden\Backup\DataTransferObjects\BackupDto;

interface BackupRepository
{
    /**
     * Get all backups.
     *
     * @return Collection<BackupDto>
     */
    public function all(): Collection;

    /**
     * Get a backup by id.
     */
    public function find(string $id): ?BackupDto;

    /**
     * Add a backup.
     */
    public function add(string $path): BackupDto;

    /**
     * Delete a backup by id.
     */
    public function remove(string $id): ?BackupDto;

    /**
     * Clear all backups.
     */
    public function empty(): bool;
}
