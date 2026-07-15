<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'memo' => ['required', 'string'],
            'date_times' => ['required', 'array', 'min:1'],
            'date_times.*' => [
                'required',
                'date_format:Y-m-d\TH:i:s\Z',
                'after_or_equal:now',
            ],
        ];
    }
}
