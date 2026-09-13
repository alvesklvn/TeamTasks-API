<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NewProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|regex:/^[a-zA-Z ]+$/|min:10|max:50',
            'description' => 'required|string|regex:/^[a-zA-ZÀ-ú0-9,.!"\' ]+$/|min:10|max:400',
            'members' => 'sometimes|array|max:30',
            'members.*' => 'required|email:rfc,dns|max:50'
        ];
    }
}
