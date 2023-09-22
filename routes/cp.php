<?php

use Illuminate\Support\Facades\Route;
use Itiden\Backup\Http\Controllers\BackupController;
use Itiden\Backup\Http\Controllers\CreateBackupController;
use Itiden\Backup\Http\Controllers\DownloadBackupController;
use Itiden\Backup\Http\Middleware\CanCreateBackups;
use Itiden\Backup\Http\Middleware\CanManageBackups;

Route::name('itiden.backup.')->prefix('backups')->group(function () {
    Route::get('/', [BackupController::class, '__invoke'])
        ->name('index');

    Route::post('/', [CreateBackupController::class, '__invoke'])
        ->middleware(CanCreateBackups::class)
        ->name('create');

    Route::get('/download/{timestamp}', [DownloadBackupController::class, '__invoke'])
        ->name('download');
})->middleware([CanManageBackups::class]);
