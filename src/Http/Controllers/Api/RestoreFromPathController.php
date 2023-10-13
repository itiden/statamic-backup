<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Itiden\Backup\Facades\Restorer;
use Itiden\Backup\Http\Requests\RestoreFromPathRequest;
use Itiden\Backup\Http\Response;

class RestoreFromPathController extends Controller
{
    public function __invoke(RestoreFromPathRequest $request): JsonResponse|RedirectResponse
    {
        Restorer::restoreFromArchive($request->validated('path'));

        return Response::success('Backup restored.');
    }
}
