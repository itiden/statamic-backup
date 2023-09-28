<?php

use Illuminate\Support\Facades\Route;
use Itiden\Backup\Http\Controllers\Api\BackupController;
use Itiden\Backup\Http\Controllers\Api\RestoreController;
use Itiden\Backup\Http\Controllers\Api\CreateBackupController;
use Itiden\Backup\Http\Controllers\DownloadBackupController;
use Itiden\Backup\Http\Middleware\CanCreateBackups;
use Itiden\Backup\Http\Middleware\CanManageBackups;
use Itiden\Backup\Http\Middleware\CanRestoreBackups;

Route::name('itiden.backup.')->prefix('backups')->group(function () {
    Route::view('/', 'itiden-backup::backups')
        ->name('index');
})->middleware([CanManageBackups::class]);

Route::name('api.itiden.backup')->prefix('api/backups')->group(function () {
    Route::post('/create', CreateBackupController::class)
        ->middleware(CanCreateBackups::class)
        ->name('create');

    Route::get('/', BackupController::class)
        ->name('index');

    Route::get('/download/{timestamp}', DownloadBackupController::class)
        ->name('download');

    Route::post('/restore/{timestamp}', RestoreController::class)
        ->middleware(CanRestoreBackups::class)
        ->name('restore');
})->middleware([CanManageBackups::class]);
