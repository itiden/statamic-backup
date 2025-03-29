<?php

declare(strict_types=1);

use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Tests\TestCase;

uses(TestCase::class)->afterEach(fn(): bool => app(BackupRepository::class)->empty())->in(__DIR__);
