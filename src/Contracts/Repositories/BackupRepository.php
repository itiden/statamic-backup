<?php

declare(strict_types=1);

namespace Itiden\Backup\Contracts\Repositories;

use Illuminate\Support\Collection;
use Itiden\Backup\DataTransferObjects\BackupDto;

interface BackupRepository
{
    /**
     * Get all backups.
     * @return Collection<BackupDto>
     */
    public function all(): Collection;

    /**
     * Get a backup by timestamp.
     */
    public function find(string $timestamp): ?BackupDto;

    /**
     * Add a backup.
     */
    public function add(string $path): BackupDto;

    /**
     * Delete a backup by timestamp.
     */
    public function remove(string $timestamp): BackupDto;

    /**
     * Clear all backups.
     */
    public function empty(): bool;
}
