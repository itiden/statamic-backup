<?php

declare(strict_types=1);

use Carbon\Carbon;
use Itiden\Backup\Facades\Backuper;

describe('dto:backup', function (): void {
    it('will resolve valid timestamp when app name has many dashes', function (): void {
        config(['app.name' => 'app-with-many-dashes']);

        $fakeTime = Carbon::now();

        Carbon::setTestNow($fakeTime);

        $backup = Backuper::backup();

        expect($backup->id)->toBeString();
        expect($backup->created_at->timestamp)->toBe($fakeTime->timestamp);
    });
})->group('backupdto');
