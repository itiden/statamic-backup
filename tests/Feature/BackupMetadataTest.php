<?php

use Illuminate\Support\Carbon;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Models\Metadata;
use Itiden\Backup\DataTransferObjects\UserActionDto;
use Itiden\Backup\Pipes\Users as UserPipe;

uses()->group('metadata')->afterEach(function () {
    app(BackupRepository::class)->empty();
});

it('can generate a metadata file for a backup', function () {
    $backup = Backuper::backup();

    $metadata = $backup->getMetadata();

    expect($metadata)->toBeInstanceOf(Metadata::class);
});

it('can get the user that created the backup', function () {
    $backup = Backuper::backup();

    $metadata = $backup->getMetadata();

    expect($metadata->getCreatedBy())->toBeNull();

    $user = user();

    $metadata->setCreatedBy($user);

    expect($metadata->getCreatedBy())->toBe($user);
});

it('can get the downloads for a backup', function () {
    Carbon::setTestNow('2021-01-01 00:00:00');
    $backup = Backuper::backup();

    $metadata = $backup->getMetadata();

    expect($metadata->getDownloads())->toBeEmpty();

    $user = user();

    $metadata->addDownload($user);

    expect($metadata->getDownloads())->toHaveCount(1);
    expect($metadata->getDownloads()->first())->toBeInstanceOf(UserActionDto::class);
    expect($metadata->getDownloads()->first()->getUser()->getAuthIdentifier())->toBe($user->getAuthIdentifier());
    expect($metadata->getDownloads()->first()->getTimestamp())->toEqual(Carbon::now());
});

it('can get the restores for a backup', function () {
    Carbon::setTestNow('2021-01-01 00:00:00');
    $backup = Backuper::backup();

    $metadata = $backup->getMetadata();

    expect($metadata->getRestores())->toBeEmpty();

    $user = user();

    $metadata->addRestore($user);

    expect($metadata->getRestores())->toHaveCount(1);
    expect($metadata->getRestores()->first())->toBeInstanceOf(UserActionDto::class);
    expect($metadata->getRestores()->first()->getUser()->getAuthIdentifier())->toBe($user->getAuthIdentifier());
    expect($metadata->getRestores()->first()->getTimestamp())->toEqual(Carbon::now());
});

it('can get the skipped pipes for a backup', function () {
    $backup = Backuper::backup();

    $metadata = $backup->getMetadata();

    expect($metadata->getSkippedPipes())->toBeEmpty();

    $metadata->addSkippedPipe(UserPipe::class);

    expect($metadata->getSkippedPipes())->toHaveCount(1);
    expect($metadata->getSkippedPipes()->first())->toBe(UserPipe::class);
});
