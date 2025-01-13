<?php

use Itiden\Backup\Abstracts\BackupPipe;

uses()->group('architecture');

arch(null)->preset()->strict()->ignoring(BackupPipe::class);
arch(null)->preset()->php();
arch(null)->preset()->security();
arch(null)->preset()->laravel();

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
