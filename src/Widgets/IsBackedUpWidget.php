<?php

declare(strict_types=1);

namespace Itiden\Backup\Widgets;

use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Statamic\Widgets\Widget;

final class IsBackedUpWidget extends Widget
{
    public function __construct(
        protected BackupRepository $repository
    ) {
    }

    public static $handle = 'is_backed_up';

    /**
     * The HTML that should be shown in the widget.
     */
    public function html()
    {
        $lastBackup = $this->repository->all()->sortByDesc('timestamp')->first();

        return view('itiden-backup::widgets.is-backed-up', [
            'lastBackup' => $lastBackup,
        ]);
    }
}
