<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddTasksRequest extends FormRequest
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
            'tasks' => 'required|array|min:1|max:40',
            'tasks.*' => 'required|array|',
            'tasks.*.email' => 'required|email:rfc,dns|exists:users,email',
            'tasks.*.name' => 'required|string|regex:/^[0-9A-Za-zÀ-ú ]+$/|min:1|max:30',
            'tasks.*.description' => 'required|string|regex:/^[0-9A-Za-zÀ-ú .,]+$/|min:10|max:255',
            'tasks.*.deadline' => 'required|date'
        ];
    }
}
