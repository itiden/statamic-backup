<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Facades\Restorer;
use Itiden\Backup\Http\Response;

class RestoreFromUploadController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $disk = Storage::build([
            'driver' => 'local',
            'root' => config('backup.temp_path')
        ]);

        $file =  $disk->putFileAs('uploads', $request->file('file'), $request->file('file')->getClientOriginalName());

        Restorer::restoreFromArchive($disk->path($file));

        $disk->deleteDirectory('uploads');

        return Response::success("Successfully restored from {$request->file('file')->getClientOriginalName()}");
    }
}
