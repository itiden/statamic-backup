<?php

use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Facades\Backuper;

use function Pest\Laravel\artisan;

describe('command:restore', function () {
    it('shows all available backups', function () {
        app(BackupRepository::class)->empty();

        Backuper::backup();

        $backups = app(BackupRepository::class)->all();

        artisan('statamic:backup:restore')
            ->expectsQuestion(
                question: 'Which backup do you want to restore to?',
                answer: $backups->first()->path
            )
            ->expectsConfirmation('Are you sure you want to restore your content?')
            ->assertFailed();
    });

    it('can restore from a specific path', function () {
        app(BackupRepository::class)->empty();

        $backup = Backuper::backup();

        artisan('statamic:backup:restore', ['--path' => Storage::disk(config('backup.destination.disk'))->path($backup->path)])
            ->expectsConfirmation('Are you sure you want to restore your content?')
            ->assertFailed();
    });
})->group('restore-command');
