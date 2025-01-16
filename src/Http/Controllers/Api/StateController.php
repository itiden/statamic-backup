<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Itiden\Backup\StateManager;

final readonly class StateController
{
    public function __invoke(StateManager $manager): JsonResponse
    {
        return response()->json([
            'state' => $manager->getState()->value,
        ]);
    }
}
