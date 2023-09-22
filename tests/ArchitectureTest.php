<?php

use Itiden\Backup\Contracts\Restorer;

uses()->group('architecture');

test('strict types')
    ->expect('Itiden\Backup')
    ->toUseStrictTypes();

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
    ->expect('Itiden\Backup\Clients')
    ->toImplement(Restorer::class);
