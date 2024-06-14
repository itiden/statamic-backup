<?php

declare(strict_types=1);

namespace Itiden\Backup\Contracts;

use Carbon\Carbon;

interface BackupNameGenerator
{
    public function generate(Carbon $createdAt): string;
}
