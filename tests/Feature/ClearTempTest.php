<?php

use Illuminate\Support\Facades\File;

uses()->group('console');

test("backup clear command clears the temp path", function () {
    $temp_path = config('backup.temp_path');

    File::put($temp_path . '/testfile.txt', 'lorem ipsum');

    expect(File::allFiles($temp_path))->toHaveCount(1);

    $this->artisan('statamic:backup:clear');

    expect(File::allFiles($temp_path))->toHaveCount(0);
});
