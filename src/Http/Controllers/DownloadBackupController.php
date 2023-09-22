<?php

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Http\Requests\DownloadBackupRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadBackupController extends Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * Handle the incoming request.
     */
    public function __invoke(DownloadBackupRequest $request, string $timestamp): StreamedResponse
    {
        $backup =  Backuper::getBackups()->first(function ($backup) use ($timestamp) {
            return $backup['timestamp'] === $timestamp;
        });

        return Storage::download($backup['path']);
    }
}
