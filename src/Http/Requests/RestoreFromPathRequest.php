<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RestoreFromPathRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'path' => 'required|string',
            'destroyAfterRestore' => 'nullable|boolean',
        ];
    }
}
