<?php

declare(strict_types=1);

namespace Itiden\Backup\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Abstracts\BackupPipe;
use Statamic\Facades\User;
use Statamic\Facades\YAML;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\DataTransferObjects\UserActionDto;

final class Metadata
{
    private Filesystem $filesystem;

    /** @var int|string|null */
    private $createdBy = null;

    /** @var UserActionDto[] */
    private array $downloads;

    /** @var UserActionDto[] */
    private array $restores;

    /** @var array<class-string<BackupPipe>> */
    private array $skippedPipes;

    public function __construct(
        private BackupDto $backup,
    ) {
        $this->filesystem = Storage::build([
            'driver' => 'local',
            'root' => storage_path() . 'statamic-backup/.metadata',
        ]);

        $yaml = YAML::parse($this->filesystem->get($this->backup->timestamp) ?? '');

        $this->createdBy = $yaml['created_by'] ?? null;
        $this->downloads = array_map(UserActionDto::fromArray(...), $yaml['downloads'] ?? []);
        $this->restores = array_map(UserActionDto::fromArray(...), $yaml['restores'] ?? []);
        $this->skippedPipes = $yaml['skipped_pipes'] ?? [];

        if ($yaml === null) {
            $this->save();
        }
    }

    public function getCreatedBy(): ?Authenticatable
    {
        return User::find($this->createdBy);
    }

    public function setCreatedBy(Authenticatable $user)
    {
        $this->createdBy = $user->getAuthIdentifier();
    }

    public function addDownload(Authenticatable $user)
    {
        $this->downloads[] = new UserActionDto(
            userId: $user->getAuthIdentifier(),
            timestamp: now()->toString(),
        );

        $this->save();
    }

    public function addRestore(Authenticatable $user)
    {
        $this->restores[] = new UserActionDto(
            userId: $user->getAuthIdentifier(),
            timestamp: now()->toString(),
        );

        $this->save();
    }

    /** @return Collection<UserActionDto> */
    public function getDownloads(): Collection
    {
        return collect($this->downloads);
    }

    /** @return Collection<UserActionDto> */
    public function getRestores(): Collection
    {
        return collect($this->restores);
    }

    public function getSkippedPipes(): Collection
    {
        return collect($this->skippedPipes);
    }

    /**
     * @param class-string<BackupPipe> $pipe
     */
    public function addSkippedPipe(string $pipe)
    {
        $this->skippedPipes[] = $pipe;

        $this->save();
    }

    public function delete()
    {
        $this->filesystem->delete($this->backup->timestamp);
    }


    private function save()
    {
        $this->filesystem->put($this->backup->timestamp, YAML::dump([
            'created_by' => $this->createdBy,
            'downloads' => array_map(fn(UserActionDto $action) => $action->toArray(), $this->downloads),
            'restores' => array_map(fn(UserActionDto $action) => $action->toArray(), $this->restores),
            'skipped_pipes' => $this->skippedPipes,
        ]));
    }
}
