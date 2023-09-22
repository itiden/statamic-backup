<?php

declare(strict_types=1);

namespace Itiden\Backup\Contracts;

use ZipArchive;

interface Restorer
{
    public static function getKey(): string;

    public function restore(string $path): bool;

    public function backup(ZipArchive $zip): bool;
}
