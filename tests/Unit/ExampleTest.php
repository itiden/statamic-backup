<?php

use Itiden\Backup\Facades\Restorer;

test('confirm environment is set to testing', function () {
    expect(config('app.env'))->toBe('testing');
});

it("test", function () {

    Restorer::restore(__DIR__);
});
