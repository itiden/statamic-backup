<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class DownloadBackupController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $timestamp, BackupRepository $repo): StreamedResponse
    {
        $backup = $repo->find($timestamp);

        $backup->getMetadata()->addDownload(auth()->user());

        return Storage::disk(config('backup.destination.disk'))->download($backup->path);
    }
}
