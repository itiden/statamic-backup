<?php

declare(strict_types=1);

namespace Itiden\Backup\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Abstracts\BackupPipe;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\DataTransferObjects\SkippedPipeDto;
use Itiden\Backup\DataTransferObjects\UserActionDto;
use Statamic\Facades\User;
use Statamic\Facades\YAML;

use function Illuminate\Filesystem\join_paths;

// @mago-expect lint:too-many-methods
final class Metadata
{
    private Filesystem $filesystem;

    private string|int|null $createdBy = null;

    /** @var UserActionDto[] */
    private array $downloads;

    /** @var UserActionDto[] */
    private array $restores;

    /** @var SkippedPipeDto[] */
    private array $skippedPipes;

    public function __construct(
        private BackupDto $backup,
    ) {
        $this->filesystem = Storage::build([
            'driver' => 'local',
            'root' => join_paths(Config::string('backup.metadata_path'), '.meta'),
        ]);

        $yaml = YAML::parse($this->filesystem->get($this->backup->id) ?? '');

        $this->createdBy = $yaml['created_by'] ?? null;
        $this->downloads = array_map(UserActionDto::fromArray(...), $yaml['downloads'] ?? []);
        $this->restores = array_map(UserActionDto::fromArray(...), $yaml['restores'] ?? []);
        $this->skippedPipes = array_map(SkippedPipeDto::fromArray(...), $yaml['skipped_pipes'] ?? []);

        if (count($yaml) === 0) {
            $this->save();
        }
    }

    public function getCreatedBy(): ?Authenticatable
    {
        return User::find($this->createdBy);
    }

    public function setCreatedBy(Authenticatable $user): void
    {
        $this->createdBy = $user->getAuthIdentifier();

        $this->save();
    }

    public function addDownload(Authenticatable $user): void
    {
        $this->downloads[] = new UserActionDto(
            userId: $user->getAuthIdentifier(),
            timestamp: now()->toString(),
        );

        $this->save();
    }

    public function addRestore(Authenticatable $user): void
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
     * @param class-string<BackupPipe> $pipe The pipe that was skipped.
     * @param string $reason The reason why the pipe was skipped.
     */
    public function addSkippedPipe(string $pipe, string $reason): void
    {
        $this->skippedPipes[] = new SkippedPipeDto(
            pipe: $pipe,
            reason: $reason,
        );

        $this->save();
    }

    public function delete(): void
    {
        $this->filesystem->delete($this->backup->id);
    }

    private function save(): void
    {
        $this->filesystem->put($this->backup->id, YAML::dump([
            'created_by' => $this->createdBy,
            'downloads' => array_map(fn(UserActionDto $action): array => $action->toArray(), $this->downloads),
            'restores' => array_map(fn(UserActionDto $action): array => $action->toArray(), $this->restores),
            'skipped_pipes' => array_map(fn(SkippedPipeDto $dto): array => $dto->toArray(), $this->skippedPipes),
        ]));
    }
}
