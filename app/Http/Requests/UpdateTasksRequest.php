<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTasksRequest extends FormRequest
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
            'name' => 'required_without_all|string|regex:/^[0-9A-Za-zÀ-ú ]+$/|min:1|max:30',
            'description' => 'required_without_all|string|regex:/^[0-9A-Za-zÀ-ú .,]+$/|min:10|max:255',
            'deadline' => 'required_without_all|date'
        ];
    }
}
