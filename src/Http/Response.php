<?php

declare(strict_types=1);

namespace Itiden\Backup\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class Response
{
    public static function error(string $message, int $code = 500): JsonResponse|RedirectResponse
    {
        if (request()->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], $code);
        }

        return redirect()->back()->with('error', $message);
    }

    public static function success(string $message): JsonResponse|RedirectResponse
    {
        if (request()->expectsJson()) {
            return response()->json([
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
