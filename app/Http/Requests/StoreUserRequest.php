<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            "name" => "required|regex:/^[a-zA-Z ]+$/",
            "username" => "required|regex:/^[a-zA-Z0-9]+[0-9]+[a-zA-Z0-9]+$/",
            "password" => "required|regex:/^[A-Z]+[a-zA-Z0-9]+[0-9]+[*$@?=]+/",
            "confirmPassword" => "required|same:password"
        ];
    }
}
