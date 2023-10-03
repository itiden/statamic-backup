<?php

use Illuminate\Support\Facades\Route;
use Itiden\Backup\Http\Controllers\Api\BackupController;
use Itiden\Backup\Http\Controllers\Api\DestroyBackupController;
use Itiden\Backup\Http\Controllers\Api\RestoreController;
use Itiden\Backup\Http\Controllers\Api\StoreBackupController;
use Itiden\Backup\Http\Controllers\DownloadBackupController;
use Itiden\Backup\Http\Controllers\RestoreFromUploadController;
use Itiden\Backup\Http\Middleware\EnsureUserCan;

Route::name('itiden.backup.')
    ->middleware(EnsureUserCan::class . ':manage backups')
    ->prefix('backups')
    ->group(function () {
        Route::view('/', 'itiden-backup::backups')
            ->name('index');

        Route::post('/', RestoreFromUploadController::class)
            ->middleware(EnsureUserCan::class . ':restore backups')
            ->name('store');
    });

Route::name('api.itiden.backup.')
    ->middleware(EnsureUserCan::class . ':manage backups')
    ->prefix('api/backups')
    ->group(function () {
        Route::get('/', BackupController::class)
            ->name('index');

        Route::post('/', StoreBackupController::class)
            ->middleware(EnsureUserCan::class . ':create backups')
            ->name('store');

        Route::delete('/{timestamp}', DestroyBackupController::class)
            ->middleware(EnsureUserCan::class . ':delete backups')
            ->name('destroy');

        Route::get('/download/{timestamp}', DownloadBackupController::class)
            ->middleware(EnsureUserCan::class . ':download backups')
            ->name('download');

        Route::post('/restore/{timestamp}', RestoreController::class)
            ->middleware(EnsureUserCan::class . ':restore backups')
            ->name('restore');
    });
