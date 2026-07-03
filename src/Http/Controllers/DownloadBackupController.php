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

        // Clean and close all active output buffers to allow streaming without running out of memory
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $disk = Storage::disk(Config::string('backup.destination.disk'));

        try {
            $path = $disk->path($backup->path);
            return response()->download($path);
        } catch (\Throwable) {
            return $disk->download($backup->path);
        }
    }
}
