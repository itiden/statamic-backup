<?php

uses()->group('widgets');

it("can see if the user is backed up", function () {
    $this->withoutVite();
    config(['statamic.cp.widgets' => ['is_backed_up']]);

    $user = user();

    $user->assignRole(
        'super admin'
    )->save();

    $this->actingAs($user)
        ->get('/cp/dashboard')
        ->assertSee(__('Not backed up.'));

    $this->artisan('statamic:backup');

    $this->actingAs($user)
        ->get('/cp/dashboard')
        ->assertSee(__('Your site was backed up'));
});
