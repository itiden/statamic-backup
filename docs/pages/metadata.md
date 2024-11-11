# Metadata

Statamic Backup create `metadata` files for each of your backups, these files contain information about who created the backup, how many and who has restored or downloaded and if a backup pipe has been skipped.

## How do I access my backups metadata?

You can get backup meta data from `\Itiden\Backup\DataTransferObjects\BackupDto` instances like this:

```php
$backup = app(\Itiden\Backup\Contracts\Repositores\BackupRepository::class)->find($timestamp);

$metadata = $backup->getMetadata() // \Itiden\Backup\Models\Metadata
```

> The metadata file will be regenerated if it is deleted or not found (it will not regenerate any data, that will be lost).

## Created by

If the backup was manually started, this users id will be attached to the metadata file.

```php
$metadata->getCreatedBy() // \Statamic\Contracts\Auth\User|null

// Or you can set the creator
$metadata->setCreatedBy(auth()->user())
```

## Skipped pipes

Some backup pipes expects some directories to exist and when they don't, what should happen? By default we will mark that pipe as skipped (which will show in the control panel), if you want another behavior you can create your own version of the pipe!

### How to interact with skipped pipes:

```php
$skippedPipes = $metadata->getSkippedPipes() // Collection<\Itiden\Backup\DataTransferObjects\SkippedPipeDto>

$skipped = $skippedPipes->first();

$skipped->pipe // class-string<\Itiden\Backup\Abstracts\BackupPipe>
$skipped->reason // string
```

## Downloads and Restores

When a backup is downloaded or restored, an entry will be added to the backups metadata with a userId and a timestamp in the form of a `\Itiden\Backup\DataTransferObjects\UserActionDto`.

### How to interact with downloads and restores

```php
$downloads = $metadata->getDownloads() // Collection<\Itiden\Backup\DataTransferObjects\UserActionDto>
$restores = $metadata->getRestores() // Collection<\Itiden\Backup\DataTransferObjects\UserActionDto>

$action = $downloads->first();

$action->userId // string
$action->timestamp // string
$action->getTimestamp() // \Carbon\CarbonImmutable
$action->getUser() // \Statamic\Contracts\Auth\User|null
```
