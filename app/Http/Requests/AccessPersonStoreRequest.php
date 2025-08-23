<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccessPersonStoreRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'full_name' => ['required','string','max:120'],
            'document'  => ['required','string','max:50'],
            'gender'    => ['nullable','in:M,F,O'],
            'role'      => ['required','in:driver,passenger,pedestrian'],
            'is_driver' => ['boolean'],
        ];
    }
}
