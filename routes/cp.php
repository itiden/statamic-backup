<?php

use Illuminate\Support\Facades\Route;
use Itiden\Backup\Http\Controllers\BackupController;
use Itiden\Backup\Http\Controllers\CreateBackupController;
use Itiden\Backup\Http\Controllers\DownloadBackupController;

Route::name('itiden.backup.')->prefix('backups')->group(function () {
    Route::get('/', [BackupController::class, '__invoke'])
        ->name('index');

    Route::post('/', [CreateBackupController::class, '__invoke'])
        ->name('create');

    Route::get('/download/{timestamp}', [DownloadBackupController::class, '__invoke'])
        ->name('download');
});
