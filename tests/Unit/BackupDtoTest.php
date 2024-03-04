<?php

use Carbon\Carbon;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Facades\Backuper;

uses()->group('backupdto');

it('will resolve valid timestamp when app name has many dashes', function () {
    config(['app.name' => 'app-with-many-dashes']);

    $fakeTime = Carbon::parse('2021-01-01 12:00:00');

    Carbon::setTestNow($fakeTime);

    $backup = Backuper::backup();

    expect($backup->timestamp)->toBeString();
    expect($backup->timestamp)->toBe((string) $fakeTime->timestamp);
});