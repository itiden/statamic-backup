<?php

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UploadBackupController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        dd($request->file('file'));

        // return redirect()->route('itiden.backup.index');
    }
}
