<?php

use Illuminate\Support\Facades\File;

uses()->group('console');

it("will not crash if the temp path doesn't exist", function () {
    $temp_path = config('backup.temp_path');

    File::deleteDirectory($temp_path);

    $this->artisan('statamic:backup:clear')->assertExitCode(0);
});

it("will clear temp path when running backup clear command", function () {
    $temp_path = config('backup.temp_path');

    File::ensureDirectoryExists($temp_path);

    File::put($temp_path . '/testfile.txt', 'lorem ipsum');

    expect(File::allFiles($temp_path))->toHaveCount(1);

    $this->artisan('statamic:backup:clear');

    expect(File::allFiles($temp_path))->toHaveCount(0);
});
