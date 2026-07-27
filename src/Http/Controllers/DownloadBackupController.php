<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Symfony\Component\HttpFoundation\Response;

final readonly class DownloadBackupController
{
    public function __invoke(Request $request, string $id, BackupRepository $repo): Response
    {
        $backup = $repo->find($id);

        if (!$backup) {
            abort(404);
        }

        /** @var Authenticatable */
        $user = $request->user();

        $backup->getMetadata()->addDownload($user);

        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }

        $disk = Storage::disk(Config::string('backup.destination.disk'));

        return response()->streamDownload(
            callback: static fn() => $disk->readStream($backup->path),
            name: $backup->name,
            headers: [
                'Content-Type' => 'application/octet-stream',
                'Content-Length' => $disk->size($backup->path),
            ],
        );
    }
}
