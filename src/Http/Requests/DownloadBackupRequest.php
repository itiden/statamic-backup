<?php

namespace Itiden\Backup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\User;

class DownloadBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return User::current()->can('download backups');
    }
}
