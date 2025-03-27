<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Models\Metadata;
use Itiden\Backup\DataTransferObjects\UserActionDto;
use Itiden\Backup\Pipes\Users as UserPipe;
use Statamic\Yaml\Yaml;

use function Itiden\Backup\Tests\user;

describe('metadata', function (): void {
    it('can generate a metadata file for a backup', function (): void {
        $backup = Backuper::backup();

        $metadata = $backup->getMetadata();

        expect($metadata)->toBeInstanceOf(Metadata::class);
    });

    it('creates a file if its missing', function (): void {
        $backup = Backuper::backup();

        $metadata = $backup->getMetadata();

        expect($metadata)->toBeInstanceOf(Metadata::class);

        $metadata->delete();

        $metadata = $backup->getMetadata();

        expect($metadata)->toBeInstanceOf(Metadata::class);
    });

    it('can get the user that created the backup', function (): void {
        $backup = Backuper::backup();

        $metadata = $backup->getMetadata();

        expect($metadata->getCreatedBy())->toBeNull();

        $user = user();

        $metadata->setCreatedBy($user);

        expect($metadata->getCreatedBy())->toBe($user);
    });

    it('can get the downloads for a backup', function (): void {
        Carbon::setTestNow('2021-01-01 00:00:00');
        $backup = Backuper::backup();

        $metadata = $backup->getMetadata();

        expect($metadata->getDownloads())->toBeEmpty();

        $user = user();

        $metadata->addDownload($user);

        expect($metadata->getDownloads())->toHaveCount(1);
        expect($metadata
            ->getDownloads()
            ->first())->toBeInstanceOf(UserActionDto::class);
        expect($metadata
            ->getDownloads()
            ->first()
            ->getUser()
            ->getAuthIdentifier())->toBe($user->getAuthIdentifier());
        expect($metadata
            ->getDownloads()
            ->first()
            ->getTimestamp())->toEqual(Carbon::now());
    });

    it('can get the restores for a backup', function (): void {
        Carbon::setTestNow('2021-01-01 00:00:00');
        $backup = Backuper::backup();

        $metadata = $backup->getMetadata();

        expect($metadata->getRestores())->toBeEmpty();

        $user = user();

        $metadata->addRestore($user);

        expect($metadata->getRestores())->toHaveCount(1);
        expect($metadata
            ->getRestores()
            ->first())->toBeInstanceOf(UserActionDto::class);
        expect($metadata
            ->getRestores()
            ->first()
            ->getUser()
            ->getAuthIdentifier())->toBe($user->getAuthIdentifier());
        expect($metadata
            ->getRestores()
            ->first()
            ->getTimestamp())->toEqual(Carbon::now());
    });

    it('can get the skipped pipes for a backup', function (): void {
        $backup = Backuper::backup();

        $metadata = $backup->getMetadata();

        expect($metadata->getSkippedPipes())->toBeEmpty();

        $metadata->addSkippedPipe(pipe: UserPipe::class, reason: 'Some reason');

        expect($metadata->getSkippedPipes())->toHaveCount(1);
        expect(
            $metadata
                ->getSkippedPipes()
                ->first()->pipe,
        )->toBe(UserPipe::class);
        expect(
            $metadata
                ->getSkippedPipes()
                ->first()->reason,
        )->toBe('Some reason');
    });

    it('stores metadata files in the correct directory', function (): void {
        $backup = Backuper::backup();

        $metadata = $backup->getMetadata();
        $metadata->addSkippedPipe(pipe: UserPipe::class, reason: 'Some reason');

        $file = File::get(config('backup.metadata_path') . '/.meta/' . $backup->id);
        $yaml = app(Yaml::class)->parse($file);

        expect($file)->not->toBeEmpty();

        expect($metadata->getSkippedPipes())->toHaveCount(1);
        expect($yaml['skipped_pipes'])->toHaveCount(1);
        expect($yaml['skipped_pipes'][0]['pipe'])->toBe(UserPipe::class);
        expect($yaml['skipped_pipes'][0]['reason'])->toBe('Some reason');
    });
})->group('metadata');
