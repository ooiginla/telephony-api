<?php

namespace App\Http\Requests;

use App\Models\Agency;
use Illuminate\Foundation\Http\FormRequest;

class UpsertQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function getAgency():Agency
    {
        return Agency::where('id', $this->agency_id)->first();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string'],
            'type' => ['required', 'string'],
            'choices' => ['required', 'array'],
            'agency_id' => ['required', 'integer', 'exists:agencies,id']
        ];
    }
}
