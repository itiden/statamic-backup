<?php

use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Tests\TestCase;

uses(TestCase::class)
    ->afterEach(fn () => app(BackupRepository::class)->empty())
    ->in(__DIR__);
