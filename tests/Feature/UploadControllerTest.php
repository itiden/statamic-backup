<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Support\Chunky;

use function Illuminate\Filesystem\join_paths;
use function Itiden\Backup\Tests\chunk_file;
use function Itiden\Backup\Tests\user;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

describe('api:upload', function (): void {
    it('can upload chunks and backup is added to repository', function (): void {
        File::cleanDirectory(config('backup.temp_path'));
        Storage::disk(config('backup.destination.disk'))->deleteDirectory(config('backup.destination.path'));

        $backup = Backuper::backup();

        $chunks = chunk_file(
            file: Storage::disk(config('backup.destination.disk'))->path(join_paths($backup->path)),
            path: config('backup.temp_path') . '/test-chunks/',
            buffer: 512,
        );

        $totalSize = Storage::disk(config('backup.destination.disk'))->size(join_paths($backup->path));

        $bodies = $chunks->map(fn(string $chunk, int $index): array => [
            'resumableIdentifier' => 'test-chunk-identifier',
            'resumableFilename' => $backup->name,
            'resumableTotalChunks' => $chunks->count(),
            'resumableChunkNumber' => $index + 1,
            'resumableTotalSize' => $totalSize,
            'file' => UploadedFile::fake()->createWithContent(basename($chunk), file_get_contents($chunk)),
        ]);

        $user = user();
        $user->makeSuper();
        $user->save();

        actingAs($user);

        $bodies
            ->take($chunks->count() - 1)
            ->each(function (array $values): void {
                $res = postJson(cp_route('itiden.backup.chunky.upload'), $values);
                $res->assertStatus(201);
                $res->assertJsonStructure(['message']);
            });

        expect(app(BackupRepository::class)->all())->toHaveCount(1);

        $res = postJson(cp_route('itiden.backup.chunky.upload'), $bodies->last());

        $res->assertSuccessful();
        expect(app(BackupRepository::class)->all())->toHaveCount(2);

        File::cleanDirectory(app(Chunky::class)->path());
        File::cleanDirectory(config('backup.temp_path'));
        app(BackupRepository::class)->empty();
    });

    it('can test if a chunk exists', function (): void {
        File::cleanDirectory(config('backup.temp_path'));
        Storage::disk(config('backup.destination.disk'))->deleteDirectory(config('backup.destination.path'));

        $backup = Backuper::backup();

        $chunks = chunk_file(
            file: Storage::disk(config('backup.destination.disk'))->path(join_paths($backup->path)),
            path: config('backup.temp_path') . '/test-chunks/',
            buffer: 512,
        );

        $totalSize = Storage::disk(config('backup.destination.disk'))->size(join_paths($backup->path));

        $bodies = $chunks->map(fn(string $chunk, int $index): array => [
            'resumableIdentifier' => 'test-chunk-identifier',
            'resumableFilename' => $backup->name,
            'resumableTotalChunks' => $chunks->count(),
            'resumableChunkNumber' => $index + 1,
            'resumableTotalSize' => $totalSize,
            'file' => UploadedFile::fake()->createWithContent(basename($chunk), file_get_contents($chunk)),
        ]);

        $user = user();
        $user->makeSuper();
        $user->save();

        actingAs($user);

        $chunksToTest = $bodies->take($chunks->count() - 1);

        $chunksToTest->each(function (array $values): void {
            $res = postJson(cp_route('itiden.backup.chunky.upload'), $values);
            $res->assertStatus(201);
        });

        $chunksToTest->each(function (array $values): void {
            $res = getJson(cp_route('itiden.backup.chunky.test', $values));
            $res->assertStatus(200);
        });

        File::cleanDirectory(app(Chunky::class)->path());
        File::cleanDirectory(config('backup.temp_path'));
        app(BackupRepository::class)->empty();
    });

    it('returns correct response when chunk doesnt exist', function () {
        File::cleanDirectory(config('backup.temp_path'));
        Storage::disk(config('backup.destination.disk'))->deleteDirectory(config('backup.destination.path'));

        $user = user();
        $user->makeSuper();
        $user->save();

        actingAs($user);

        $res = getJson(cp_route('itiden.backup.chunky.test', [
            'resumableIdentifier' => 'test-chunk-identifier',
            'resumableFilename' => 'test-file.txt',
            'resumableChunkNumber' => 1,
        ]));

        $res->assertStatus(404);
    });
});
