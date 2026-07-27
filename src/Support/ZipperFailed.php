<?php

declare(strict_types=1);

namespace Itiden\Backup\Support;

use Exception;

final class ZipperFailed extends Exception
{
    public static function toOpen(string $path, int $errorCode): self
    {
        return new self("Failed to open zip file at path: {$path}. Error code: {$errorCode}");
    }

    public static function toAddFile(string $filePath): self
    {
        return new self("Failed to add file to zip archive: {$filePath}");
    }

    public static function toAddDirectory(string $directoryPath): self
    {
        return new self("Failed to add directory to zip archive: {$directoryPath}");
    }

    public static function toClose(string $path): self
    {
        return new self("Failed to close zip file at path: {$path}");
    }

    public static function toSetEncryption(string $path): self
    {
        return new self("Failed to set encryption for zip file at path: {$path}");
    }

    public static function toSetPassword(string $path): self
    {
        return new self("Failed to set password for zip file at path: {$path}");
    }

    public static function toExtract(string $path, string $destination): self
    {
        return new self("Failed to extract zip file at path: {$path} to destination: {$destination}");
    }
}
