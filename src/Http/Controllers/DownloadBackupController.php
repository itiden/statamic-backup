<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class DownloadBackupController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $id, BackupRepository $repo): StreamedResponse
    {
        $backup = $repo->find($id);

        $backup->getMetadata()->addDownload(auth()->user());

        return Storage::disk(Config::string('backup.destination.disk'))->download($backup->path);
    }
}
