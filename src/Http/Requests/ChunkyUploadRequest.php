<?php

declare(strict_types=1);

namespace Itiden\Backup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ChunkyUploadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'resumableIdentifier' => 'required|string',
            'resumableFilename' => 'required|string',
            'resumableTotalChunks' => 'required|integer',
            'resumableChunkNumber' => 'required|integer',
            'resumableTotalSize' => 'required|integer',
            'file' => 'required|file',
        ];
    }
}
