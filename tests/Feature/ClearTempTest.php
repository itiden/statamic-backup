<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

describe('command:clear-temp', function (): void {
    it("will not crash if the temp path doesn't exist", function (): void {
        $temp_path = config('backup.temp_path');

        File::deleteDirectory($temp_path);

        $this
            ->artisan('statamic:backup:temp-clear')
            ->assertExitCode(0);
    });

    it('will clear temp path when running backup clear command', function (): void {
        $temp_path = config('backup.temp_path');

        File::ensureDirectoryExists($temp_path);

        File::put($temp_path . '/testfile.txt', 'lorem ipsum');

        expect(File::files($temp_path))->toHaveCount(1);

        $this->artisan('statamic:backup:temp-clear');

        expect(File::files($temp_path))->toHaveCount(0);
    });
})->group('console');
