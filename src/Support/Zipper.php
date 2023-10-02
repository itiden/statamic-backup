<?php

declare(strict_types=1);

namespace Itiden\Backup\Support;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;
use ZipArchive;

class Zipper
{
    private ZipArchive $zip;

    public function __construct(string $path, int $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE)
    {
        File::ensureDirectoryExists(dirname($path));

        $this->zip = new ZipArchive();

        $this->zip->open($path, $flags);
    }

    /**
     * Create a new instance of the Zipper.
     */
    public static function make(string $path, bool $readOnly = false): self
    {
        $flags = $readOnly ? ZipArchive::RDONLY : ZipArchive::CREATE | ZipArchive::OVERWRITE;

        return new static($path, $flags);
    }

    /**
     * Close the Zipper and write the archive to the filesystem.
     */
    public function close(): void
    {
        $this->zip->close();
    }

    /**
     * Encrypt the archive with the given password.
     */
    public function encrypt(string $password): self
    {
        $this->zip->setPassword($password);

        collect(range(0, $this->zip->numFiles - 1))->each(fn ($file) => $this->zip->setEncryptionIndex($file, ZipArchive::EM_AES_256));

        return $this;
    }

    /**
     * Add a file to the archive.
     */
    public function addFile(string $path, string $name = null): self
    {
        $this->zip->addFile($path, $name ?? basename($path));

        return $this;
    }

    /**
     * Add a file to the archive from a string.
     */
    public function addFromString(string $name, string $content): self
    {
        $this->zip->addFromString($name, $content);

        return $this;
    }

    /**
     * Add a directory to the archive.
     */
    public function addDirectory(string $path, string $prefix = null): self
    {
        collect(File::allFiles($path))->each(function (SplFileInfo $file) use ($prefix) {
            $this->addFile($file->getPathname(), $prefix . '/' . $file->getRelativePathname());
        });

        return $this;
    }

    /**
     * Extract the Zipper to the given path.
     */
    public function extractTo(string $path, ?string $password = null): self
    {
        if ($password) {
            $this->zip->setPassword($password);
        }

        $this->zip->extractTo($path);

        return $this;
    }
}
