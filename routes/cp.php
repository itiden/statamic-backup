<?php

use Illuminate\Support\Facades\Route;
use Itiden\Backup\Http\Controllers\Api\BackupController;
use Itiden\Backup\Http\Controllers\Api\DestroyBackupController;
use Itiden\Backup\Http\Controllers\Api\RestoreController;
use Itiden\Backup\Http\Controllers\Api\RestoreFromPathController;
use Itiden\Backup\Http\Controllers\Api\StoreBackupController;
use Itiden\Backup\Http\Controllers\DownloadBackupController;
use Itiden\Backup\Http\Controllers\UploadController;

Route::name('itiden.backup.')
    ->middleware('can:manage backups')
    ->prefix('backups')
    ->group(function () {
        Route::view('/', 'itiden-backup::backups')
            ->name('index');

        Route::name('chunky.')
            ->prefix('chunky')
            ->middleware('can:restore backups')
            ->group(function () {
                Route::post('/', UploadController::class)
                    ->name('upload');

                Route::get('/test', [UploadController::class, 'test'])
                    ->name('test');
            });
    });

Route::name('api.itiden.backup.')
    ->middleware('can:manage backups')
    ->prefix('api/backups')
    ->group(function () {
        Route::get('/', BackupController::class)
            ->name('index');

        Route::post('/', StoreBackupController::class)
            ->middleware('can:create backups')
            ->name('store');

        Route::delete('/{timestamp}', DestroyBackupController::class)
            ->middleware('can:delete backups')
            ->name('destroy');

        Route::get('/download/{timestamp}', DownloadBackupController::class)
            ->middleware('can:download backups')
            ->name('download');

        Route::post('/restore/{timestamp}', RestoreController::class)
            ->middleware('can:restore backups')
            ->name('restore');

        Route::post('/restore-from-path', RestoreFromPathController::class)
            ->middleware('can:restore backups')
            ->name('restore-from-path');
    });
