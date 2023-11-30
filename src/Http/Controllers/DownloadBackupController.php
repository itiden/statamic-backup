<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadBackupController extends Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * Handle the incoming request.
     */
    public function __invoke(string $timestamp, BackupRepository $repo): StreamedResponse
    {
        $backup = $repo->find($timestamp);

        return Storage::disk(config('backup.backup.disk'))->download($backup->path);
    }
}
