<?php

use Illuminate\Support\Facades\Route;
use Itiden\Backup\Http\Controllers\Api\BackupController;
use Itiden\Backup\Http\Controllers\CreateBackupController;
use Itiden\Backup\Http\Controllers\DownloadBackupController;
use Itiden\Backup\Http\Middleware\CanCreateBackups;
use Itiden\Backup\Http\Middleware\CanManageBackups;

Route::name('itiden.backup.')->prefix('backups')->group(function () {
    Route::view('/', 'itiden-backup::backups')
        ->name('index');

    Route::post('/create', CreateBackupController::class)
        ->middleware(CanCreateBackups::class)
        ->name('create');

    Route::get('/download/{timestamp}', DownloadBackupController::class)
        ->name('download');
})->middleware([CanManageBackups::class]);

Route::name('api.itiden.backup')->prefix('api/backups')->group(function () {
    Route::get('/', BackupController::class)
        ->name('index');
})->middleware([CanManageBackups::class]);
