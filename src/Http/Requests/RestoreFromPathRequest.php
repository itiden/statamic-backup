<?php

namespace Itiden\Backup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestoreFromPathRequest extends FormRequest
{
    public function rules()
    {
        return [
            'path' => 'required|string',
        ];
    }
}
