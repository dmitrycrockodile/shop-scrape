<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string',
        ];
    }

    public function messages() { 
        return [
            'email.required' => 'Please write the email',
            'email.email' => 'Please write a valid email address',
            'email.exists' => 'There is no user with this email',
            'password.required' => 'Please write the password',
            'password.string' => 'Password must be a valid data type',
        ];
    }
}
