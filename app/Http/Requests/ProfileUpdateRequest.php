<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
            ],
            'current_password' => [
                'string',
                'nullable',
                \Illuminate\Validation\Rule::requiredIf(function () {
                    return $this->input('new_password');
                }),
                function ($attribute, $value, $fail) {
                    if ($this->input('new_password') && $value && !Hash::check($value, Auth::user()->password)) {
                        $fail('The current password is invalid');
                    }
                },
            ],
            'new_password' => [
                'confirmed',
                'string',
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && Hash::check($value, Auth::user()->password)) {
                        $fail('New Password cannot be same as your current password');
                    }
                },
            ],
        ];
    }
}
