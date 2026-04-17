<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class DownloadBackupController
{
    public function __invoke(Request $request, string $id, BackupRepository $repo): StreamedResponse
    {
        $backup = $repo->find($id);

        if (!$backup) {
            abort(404);
        }

        /** @var Authenticatable */
        $user = $request->user();

        $backup->getMetadata()->addDownload($user);

        return Storage::disk(Config::string('backup.destination.disk'))->download($backup->path);
    }
}
