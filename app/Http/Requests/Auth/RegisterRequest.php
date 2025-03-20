<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(12)
                        ->mixedCase()
                        ->letters()
                        ->numbers()
                        ->symbols()
            ]
        ];
    }

    public function messages() { 
        return [
            'name.required' => 'Please write the name',
            'name.string' => 'Name must be a valid data type',
            'email.required' => 'Please write the email',
            'email.email' => 'Please write a valid email address',
            'email.unique' => 'This email is already in use',
            'password.required' => 'Please write the password',
            'password.confirmed' => 'Confirmation password does not match',
            'password.min' => 'Password must be at least 12 characters long.',
            'password.mixedCase' => 'Password must contain both uppercase and lowercase letters.',
            'password.letters' => 'Password must contain at least one letter.',
            'password.numbers' => 'Password must contain at least one number.',
            'password.symbols' => 'Password must contain at least one special character.',
        ];
    }
}
