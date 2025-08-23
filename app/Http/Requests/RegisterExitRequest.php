<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterExitRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'driver_person_id' => ['nullable','integer','exists:access_people,id'],
            'exit_note'        => ['nullable','string','max:255'],
        ];
    }
}
