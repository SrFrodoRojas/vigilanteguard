<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccessStoreRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'type'           => ['required','in:vehicle,pedestrian'],
            'plate'          => ['required_if:type,vehicle','nullable','string','max:20'],
            'marca_vehiculo' => ['nullable','string','max:50'],
            'color_vehiculo' => ['nullable','string','max:30'],
            'tipo_vehiculo'  => ['nullable','string','max:30'],
            'entry_note'     => ['nullable','string','max:255'],
            // ocupantes (opcional)
            'people'                 => ['array'],
            'people.*.full_name'     => ['required','string','max:120'],
            'people.*.document'      => ['required','string','max:50'],
            'people.*.gender'        => ['nullable','in:M,F,O'],
            'people.*.role'          => ['required','in:driver,passenger,pedestrian'],
            'people.*.is_driver'     => ['boolean'],
        ];
    }
}
