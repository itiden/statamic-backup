<?php

declare(strict_types=1);

namespace Itiden\Backup\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use SensitiveParameter;
use Symfony\Component\Finder\Finder;
use ZipArchive;

// @mago-expect lint:too-many-methods
final class Zipper
{
    /**
     * File extensions that are already compressed and should be stored
     * without re-compression to save CPU cycles and I/O bandwidth.
     */
    private const STORED_EXTENSIONS = [
        'zip', 'mp4', 'webm', 'png', 'jpg', 'jpeg', 'webp', 'gif', 'pdf',
        'mp3', 'wav', 'mov', 'avi', 'ogg', 'gz', 'tar', 'tgz',
        'woff', 'woff2', 'ttf', 'otf',
        'ico', 'avif', 'heic',
        'bz2', 'xz', '7z', 'rar',
    ];

    private ZipArchive $zip;
    private array $meta = [];
    private string $path;

    public function __construct(string $path, int $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE)
    {
        File::ensureDirectoryExists(dirname($path));

        $this->path = $path;
        $this->zip = new ZipArchive();

        $result = $this->zip->open($path, $flags);

        if ($result !== true) {
            throw new RuntimeException("Failed to open zip [{$path}] (error code: {$result})");
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
        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            return false;
        }

        $valid = $zip->numFiles > 0;

        $zip->close();

        return $valid;
    }

    /**
     * Close the Zipper and write the archive to the filesystem.
     */
    public function close(): void
    {
        if (!$this->zip->close()) {
            Log::error('zipper: close failed', ['path' => $this->path]);

            throw new RuntimeException(
                "Failed to write zip archive [{$this->path}] — check disk space and memory limits.",
            );
        }
    }

    /**
     * Encrypt the archive with the given password.
     */
    public function encrypt(#[SensitiveParameter] string $password): self
    {
        $this->zip->setPassword($password);

        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            if (!$this->zip->setEncryptionIndex($i, ZipArchive::EM_AES_256)) {
                throw new RuntimeException("Failed to set encryption for file at index {$i}");
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
            throw new RuntimeException("Failed to add file to zip: {$path}");
        }

        $extension = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
        $method = in_array($extension, self::STORED_EXTENSIONS, true)
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
            throw new RuntimeException("Failed to add content to zip: {$name}");
        }

        return $this;
    }

    /**
     * Add a directory to the archive.
     */
    public function addDirectory(string $path, ?string $prefix = null): self
    {
        $finder = (new Finder())->files()->ignoreDotFiles(false)->in($path);

        $count = 0;

        foreach ($finder as $file) {
            $this->addFile($file->getPathname(), $prefix . '/' . $file->getRelativePathname());

            $count++;

            if ($count % 500 === 0) {
                Log::info('zipper: addDirectory progress', [
                    'directory' => $path,
                    'files_added' => $count,
                ]);
            }
        }

        Log::info('zipper: addDirectory complete', [
            'directory' => $path,
            'total_files' => $count,
        ]);

        return $this;
    }

    /**
     * Extract the Zipper to the given path.
     */
    public function extractTo(string $path, #[SensitiveParameter] ?string $password = null): self
    {
        if ($password) {
            $this->zip->setPassword($password);
        }

        $this->zip->extractTo($path);

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
            $this->meta = json_decode($comment, true);
        }

        return collect($this->meta);
    }
}
