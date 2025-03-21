<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateRequest extends FormRequest
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
         'name' => 'nullable|string|max:255',
         'email' => 'nullable|email|unique:users,email|max:255',
         'password' => [
            'nullable',
            Password::min(12)
                  ->mixedCase()
                  ->letters()
                  ->numbers()
                  ->symbols()
         ],
         'location' => 'nullable|string'
      ];
   }

   public function messages()
   {
      return [
         'name.required' => 'Please write the user name',
         'name.string' => 'User name must be a valid string',
         'name.max' => 'User name must be less than 255 characters',
         'email.required' => 'Please write the user email',
         'email.email' => 'User email must be valid',
         'email.unique' => 'User with this email already exist',
         'email.max' => 'User email must be less than 255 characters',
         'password.required' => 'Please write the password',
         'password.min' => 'Password must be at least 12 characters long.',
         'password.mixedCase' => 'Password must contain both uppercase and lowercase letters.',
         'password.letters' => 'Password must contain at least one letter.',
         'password.numbers' => 'Password must contain at least one number.',
         'password.symbols' => 'Password must contain at least one special character.',
         'location.string' => 'User location must be a valid string'
      ];
   }
}
