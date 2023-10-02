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
    $this->withoutExceptionHandling();

    actingAs(user());

    get(cp_route('itiden.backup.index'))
        ->assertRedirect();
    getJson(cp_route('itiden.backup.index'))
        ->assertForbidden();
});

test('user with permission can view backups', function () {
    $this->withoutVite();
    $this->withoutExceptionHandling();

    $user = user();

    $user->set('roles', ['admin'])->save();

    actingAs($user);

    get(cp_route('itiden.backup.index'))
        ->assertOk()
        ->assertViewIs('itiden-backup::backups');
});
