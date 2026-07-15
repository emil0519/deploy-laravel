<?php

namespace App\Http\Requests;

use App\Models\Answer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEventParticipantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'participant_name' => ['required', 'string', 'max:255'],
            'responses' => ['required', 'array', 'min:1'],
            'responses.*.event_option_uuid' => [
                'required',
                'distinct',
                Rule::exists('event_options', 'uuid')->where('event_id', $this->route('event')->id),
            ],
            'responses.*.answer' => ['required', Rule::enum(Answer::class)],
        ];
    }
}
