<?php

declare(strict_types=1);

namespace Itiden\Backup\Stores;

use Illuminate\Support\Facades\File;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Symfony\Component\Finder\SplFileInfo;

final class BackupStore extends BasicStore
{
    public function __construct()
    {
        $this->directory = storage_path('backups');
    }

    public function key()
    {
        return 'backups';
    }

    public function fileExtension(): string
    {
        return 'backup.yaml';
    }

    /**
     * @param string $path
     * @param string $contents
     */
    public function makeItemFromFile($path, $contents): BackupDto
    {
        return BackupDto::fromArray(
            YAML::parse($contents)
        );
    }

    /**
     * @param BackupDto $item
     */
    protected function writeItemToDisk($item)
    {
        $data = YAML::dump($item->toArray());

        File::ensureDirectoryExists($this->directory);

        File::put($item->path(), $data);
    }

    protected function deleteItemFromDisk($item)
    {
        File::delete($item->path());
    }

    /**
     * @param SplFileInfo $file
     */
    public function getItemFilter($file): bool
    {
        return $file->getExtension() === $this->fileExtension();
    }
}
