<?php

declare(strict_types=1);

use Itiden\Backup\Abstracts\BackupPipe;

describe('arch', function (): void {
    arch()
        ->preset()
        ->strict()
        ->ignoring(BackupPipe::class);
    arch()
        ->preset()
        ->php();
    arch()
        ->preset()
        ->security();
    arch()
        ->preset()
        ->laravel();

    test('dtos are readonly')
        ->expect('Itiden\Backup\DataTransferObjects')
        ->classes()
        ->toBeReadonly();

    test('contracts are interfaces')
        ->expect('Itiden\Backup\Contracts')
        ->toBeInterfaces();

    test('controllers are invokable')
        ->expect('Itiden\Backup\Http\Controllers')
        ->toBeInvokable();

    test('clients implements correct contract')
        ->expect('Itiden\Backup\Pipes')
        ->toExtend(BackupPipe::class);
})->group('architecture');
