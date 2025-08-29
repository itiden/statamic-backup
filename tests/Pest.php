<?php

declare(strict_types=1);

use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Tests\TestCase;

// @mago-expect lint:prefer-first-class-callable
uses(TestCase::class)->afterEach(fn() => app(BackupRepository::class)->empty())->in(__DIR__);
