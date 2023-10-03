<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Facades\Backuper;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses()->group('restore-from-upload')->afterEach(function () {
    File::cleanDirectory(config('backup.temp_path'));
});

it('can restore from upload', function () {
    $backup = Backuper::backup();

    $user = user();

    $user->assignRole('super admin')->save();

    actingAs($user);

    $path = Storage::disk(config('backup.destination.disk'))->path($backup->path);

    $response = postJson(cp_route('itiden.backup.restore.upload'), [
        'file' => new UploadedFile($path, 'backup.zip', null, null, true),
    ]);

    expect($response->status())->toBe(200);
});

it("will not restore wrong file types", function ($file) {
    $user = user();

    $user->assignRole('super admin')->save();

    actingAs($user);

    $response = postJson(cp_route('itiden.backup.restore.upload'), [
        'file' => $file,
    ]);

    expect($response->status())->toBe(500);
})->with([
    UploadedFile::fake()->image('backup.jpg'),
    UploadedFile::fake()->create('backup.txt', 1, 'text/plain'),
]);

it("will not restore empty archives", function () {
    $user = user();

    $user->assignRole('super admin')->save();

    actingAs($user);

    $response = postJson(cp_route('itiden.backup.restore.upload'), [
        'file' => UploadedFile::fake()->create('backup.zip', 1, 'application/zip'),
    ]);

    expect($response->status())->toBe(500);
});
