<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // profile fields are validated in controllers; accept most fields here
            'f_name' => ['nullable','string','max:50'],
            's_name' => ['nullable','string','max:50'],
        ];
    }
}
