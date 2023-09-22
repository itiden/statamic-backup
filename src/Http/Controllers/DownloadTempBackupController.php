<?php

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Itiden\Backup\Facades\Backuper;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadBackupController extends Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * Handle the incoming request.
     */
    public function __invoke(): BinaryFileResponse
    {
        return Response::download(Backuper::backup())->deleteFileAfterSend(true);
    }
}
