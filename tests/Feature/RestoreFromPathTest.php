<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Events\RestoreFailed;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Support\Zipper;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('api:restore-from-upload', function () {
    it('can restore from path', function () {
        $backup = Backuper::backup();

        $user = user();

        $user->assignRole('super admin')->save();

        actingAs($user);

        $path = Storage::disk(config('backup.destination.disk'))->path($backup->path);

        $response = postJson(cp_route('api.itiden.backup.restore-from-path'), [
            'path' => $path,
        ]);

        expect($response->status())->toBe(Response::HTTP_OK);
    });

    it('can restore from path and delete after', function () {
        $backup = Backuper::backup();

        $user = user();

        $user->assignRole('super admin')->save();

        actingAs($user);

        $path = Storage::disk(config('backup.destination.disk'))->path($backup->path);

        $response = postJson(cp_route('api.itiden.backup.restore-from-path'), [
            'path' => $path,
            'destroyAfterRestore' => true,
        ]);

        expect($response->status())->toBe(Response::HTTP_OK);
        expect(File::exists($path))->toBeFalse();
    });

    it("will not restore empty archives and dispatches failed event", function () {
        Event::fake();
        $user = user();

        $emptyArchive = storage_path(config('backup.temp_path') . '/empty.zip');

        // The zip file cant be empty, but when extracting it can if the password is wrong.
        Zipper::open($emptyArchive)
            ->addFromString('empty.txt', 'empty')
            ->encrypt('notthepasswordwedecryptwith')
            ->close();

        $user->assignRole('super admin')->save();

        actingAs($user);

        $response = postJson(cp_route('api.itiden.backup.restore-from-path'), [
            'path' => $emptyArchive,
            'destroyAfterRestore' => true,
        ]);

        Event::assertDispatched(RestoreFailed::class);

        expect($response->status())->toBe(Response::HTTP_INTERNAL_SERVER_ERROR);
    });
})->group('restore-from-path')
    ->afterEach(function () {
        File::cleanDirectory(config('backup.temp_path'));
    });
