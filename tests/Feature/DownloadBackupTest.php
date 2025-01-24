<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Facades\Backuper;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

describe('api:download', function () {
    it('cant be downloaded by a guest', function (): void {
        $backup = Backuper::backup();

        $responseJson = getJson(cp_route('api.itiden.backup.download', $backup->timestamp));

        expect($responseJson->status())->toBe(401);
    });

    it('cant be downloaded by a user without permissons a backup', function (): void {
        $backup = Backuper::backup();

        actingAs(user());

        $responseJson = getJson(cp_route('api.itiden.backup.download', $backup->timestamp));

        expect($responseJson->status())->toBe(403);
    });

    it('can be downloaded by a user with download backups permission', function (string $disk): void {
        Storage::fake($disk);
        config()->set('backup.destination.disk', $disk);

        $backup = Backuper::backup();

        $user = user();

        $user
            ->assignRole('admin')
            ->save();

        actingAs($user);

        $response = get(cp_route('api.itiden.backup.download', $backup->timestamp));

        expect($response)->assertDownload();
    })->with(['s3', 'local']);

    it('adds a download action to the metadata', function (): void {
        $backup = Backuper::backup();

        $user = user();

        $user
            ->assignRole('admin')
            ->save();

        actingAs($user);

        get(cp_route('api.itiden.backup.download', $backup->timestamp));

        $metadata = $backup->getMetadata();

        expect($metadata->getDownloads())->toHaveCount(1);
        expect($metadata->getDownloads()[0]->getUser()->id)->toBe($user->id);
    });
})->group('download backup');
