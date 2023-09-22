<?php

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Routing\Controller;
use Itiden\Backup\Facades\Backuper;

class BackupController extends Controller
{
    public function __invoke()
    {
        $backups = Backuper::getBackups();

        return view('itiden-backup::backups', [
            'backups' => $backups,
        ]);
    }
}
