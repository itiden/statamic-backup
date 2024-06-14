<?php

declare(strict_types=1);

namespace Itiden\Backup\Actions;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Itiden\Backup\Contracts\BackupNameGenerator;

final class GenerateName implements BackupNameGenerator
{
    public function generate(Carbon $createdAt): string
    {
        return Str::slug(config('app.name')) . '-' . $createdAt->format('Y-m-d-H-i-s');
    }
}
