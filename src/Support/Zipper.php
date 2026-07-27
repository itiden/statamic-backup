<?php

declare(strict_types=1);

namespace Itiden\Backup\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use SensitiveParameter;
use Symfony\Component\Finder\Finder;
use ZipArchive;

use function Illuminate\Filesystem\join_paths;

// @mago-expect lint:too-many-methods,cyclomatic-complexity
final class Zipper
{
    /**
     * File extensions that are already compressed and should be stored
     * without re-compression to save CPU cycles and I/O bandwidth.
     */
    private const ENCRYPTED_FILE_TYPES = [
        'zip',
        'mp4',
        'webm',
        'png',
        'jpg',
        'jpeg',
        'webp',
        'gif',
        'pdf',
        'mp3',
        'wav',
        'mov',
        'avi',
        'ogg',
        'gz',
        'tar',
        'tgz',
        'woff',
        'woff2',
        'ttf',
        'otf',
        'ico',
        'avif',
        'heic',
        'bz2',
        'xz',
        '7z',
        'rar',
    ];

    private readonly ZipArchive $zip;
    private array $meta = [];

    public function __construct(
        private readonly string $path,
        int $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE,
    ) {
        File::ensureDirectoryExists(dirname($path));

        $this->zip = new ZipArchive();

        $result = $this->zip->open($path, $flags);

        if ($result !== true) {
            throw ZipperFailed::toOpen($path, $result);
        }
    }

    /**
     * Create a new instance of the Zipper.
     */
    public static function write(string $path): self
    {
        return new static($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    }

    public static function read(string $path): self
    {
        return new static($path, ZipArchive::RDONLY);
    }

    /**
     * Verify that a zip file at the given path is a valid archive.
     */
    public static function verify(string $path): bool
    {
        try {
            if (!File::exists($path)) {
                return false;
            }

            if (File::mimeType($path) !== 'application/zip') {
                return false;
            }

            $zip = self::read($path);

            $valid = $zip->getArchive()->status === ZipArchive::ER_OK;

            $zip->close();

            return $valid;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Close the Zipper and write the archive to the filesystem.
     */
    public function close(): void
    {
        if (!$this->zip->close()) {
            throw ZipperFailed::toClose($this->path);
        }
    }

    /**
     * Encrypt the archive with the given password.
     */
    public function encrypt(#[SensitiveParameter] string $password): self
    {
        $this->zip->setPassword($password);

        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $encrypted = $this->zip->setEncryptionIndex($i, ZipArchive::EM_AES_256);

            if (!$encrypted) {
                throw ZipperFailed::toSetEncryption($this->path);
            }
        }

        return $this;
    }

    /**
     * Add a file to the archive.
     *
     * Pre-compressed file types (images, videos, archives) are stored
     * without re-compression using CM_STORE for maximum I/O performance.
     * Text-based files use CM_DEFLATE for size reduction.
     */
    public function addFile(string $path, ?string $name = null): self
    {
        $entryName = $name ?? basename($path);

        if (!$this->zip->addFile($path, $entryName)) {
            throw ZipperFailed::toAddFile($path);
        }

        $extension = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
        $method = in_array($extension, self::ENCRYPTED_FILE_TYPES, true)
            ? ZipArchive::CM_STORE
            : ZipArchive::CM_DEFLATE;

        $this->zip->setCompressionName($entryName, $method);

        return $this;
    }

    /**
     * Add a file to the archive from a string.
     */
    public function addFromString(string $name, string $content): self
    {
        if (!$this->zip->addFromString($name, $content)) {
            throw new ZipperFailed("Failed to add content from string to zip: {$name}");
        }

        return $this;
    }

    /**
     * Add a directory to the archive.
     */
    public function addDirectory(string $path, ?string $prefix = null): self
    {
        $finder = new Finder();
        $finder->files()->ignoreDotFiles(false)->in($path);

        foreach ($finder as $file) {
            $this->addFile($file->getPathname(), join_paths($prefix, $file->getRelativePathname()));
        }

        return $this;
    }

    /**
     * Extract the Zipper to the given path.
     */
    public function extractTo(string $path, #[SensitiveParameter] ?string $password = null): self
    {
        if ($password) {
            $result = $this->zip->setPassword($password);

            if (!$result) {
                throw ZipperFailed::toSetPassword($this->path);
            }
        }

        $res = $this->zip->extractTo($path);

        if (!$res) {
            throw ZipperFailed::toExtract($this->path, $path);
        }

        return $this;
    }

    /**
     * Get the ZipArchive instance.
     */
    public function getArchive(): ZipArchive
    {
        return $this->zip;
    }

    /**
     * Add some data so that it can be extracted later.
     */
    public function addMeta(string $key, array|string $meta): self
    {
        $this->meta[$key] = is_array($meta) ? array_merge($this->meta[$key] ?? [], $meta) : $meta;

        $this->zip->setArchiveComment(comment: json_encode($this->meta));

        return $this;
    }

    /**
     * Get the meta data associated with the Zipper instance.
     *
     * @return Collection<string, array>
     */
    public function getMeta(): Collection
    {
        $comment = $this->zip->getArchiveComment();

        if ($comment) {
            $this->meta = json_decode($comment, associative: true, flags: JSON_THROW_ON_ERROR);
        }

        return collect($this->meta);
    }
}
