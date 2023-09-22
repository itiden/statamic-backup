<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers;

use Illuminate\Routing\Controller;
use Itiden\Backup\Facades\Backuper;

class CreateBackupController extends Controller
{
    public function __invoke()
    {
        $backup = Backuper::backup();

        return redirect()->back()->with('success', 'Backup created ' . $backup->name);
    }
}
