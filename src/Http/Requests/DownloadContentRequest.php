<?php

namespace Itiden\Backup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class DownloadContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return User::current()->can('download content');
    }

    public function rules(): array
    {
        return [
            'include-images' => 'nullable|boolean',
        ];
    }
}
