<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddMembersRequest extends FormRequest
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
            'members' => 'required|array|max:40',
            'members.*' => 'required|array:email,is_admin',
            'members.*.email' => 'required|email:rfc,dns|exists:users,email',
            'members.*.is_admin' => 'sometimes|boolean'
        ];
    }
}
