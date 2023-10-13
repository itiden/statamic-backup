<?php

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

uses()->group('view');

test('guest cant view backups', function () {
    $this->get(cp_route('itiden.backup.index'))
        ->assertRedirect(cp_route('login'));
});

test('user without permission cant view backups', function () {
    $this->withoutVite();

    actingAs(user());

    get(cp_route('itiden.backup.index'))
        ->assertRedirect();
    getJson(cp_route('itiden.backup.index'))
        ->assertForbidden();
});

test('user with permission can view backups', function () {
    $this->withoutVite();

    $user = user();

    $user->set('roles', ['admin'])->save();

    actingAs($user);

    get(cp_route('itiden.backup.index'))
        ->assertOk()
        ->assertViewIs('itiden-backup::backups');
});

test('user without permission cant get backups from api', function () {
    $this->withoutVite();

    $user = user();

    actingAs($user);

    getJson(cp_route('api.itiden.backup.index'))
        ->assertForbidden();
});

test('user with permission can get backups from api', function () {
    $this->withoutVite();

    $user = user();

    $user->set('roles', ['admin'])->save();

    actingAs($user);

    getJson(cp_route('api.itiden.backup.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'name',
                    'size',
                    'path',
                    'created_at',
                    'timestamp',
                ],
            ],
            'meta' => [
                'columns' => [
                    '*' => [
                        'label',
                        'field',
                        'visible',
                    ],
                ]
            ]
        ]);
});
