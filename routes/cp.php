<?php

use Illuminate\Support\Facades\Route;
use Itiden\Backup\Http\Controllers\Api\BackupController;
use Itiden\Backup\Http\Controllers\Api\DestroyBackupController;
use Itiden\Backup\Http\Controllers\Api\RestoreController;
use Itiden\Backup\Http\Controllers\Api\StoreBackupController;
use Itiden\Backup\Http\Controllers\DownloadBackupController;
use Itiden\Backup\Http\Middleware\HasPermission;

Route::name('itiden.backup.')->prefix('backups')->group(function () {
    Route::view('/', 'itiden-backup::backups')
        ->name('index');
})->middleware([HasPermission::class . ':manage backups']);

Route::name('api.itiden.backup.')->prefix('api/backups')->group(function () {
    Route::get('/', BackupController::class)
        ->name('index');

    Route::post('/', StoreBackupController::class)
        ->middleware(HasPermission::class . ':create backups')
        ->name('store');

    Route::delete('/{timestamp}', DestroyBackupController::class)
        ->middleware(HasPermission::class . ':delete backups')
        ->name('destroy');

    Route::get('/download/{timestamp}', DownloadBackupController::class)
        ->middleware(HasPermission::class . ':download backups')
        ->name('download');

    Route::post('/restore/{timestamp}', RestoreController::class)
        ->middleware(HasPermission::class . ':restore backups')
        ->name('restore');
})->middleware(HasPermission::class . ':manage backups');
