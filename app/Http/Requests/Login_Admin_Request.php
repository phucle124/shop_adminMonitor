<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class Login_Admin_Request extends FormRequest
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
            //
            'email' => 'required|string|max:50',
            'matkhau' => 'required|string|min:8|max:100',
        ];
    }

    public function validate_Value(Validator $validator){
        $validator->after(function (Validator $validator){

            $email = trim($this->input('email'));

            //Email
            $validEmail = filter_var($email,FILTER_VALIDATE_EMAIL);
            if(!$validEmail){
                $validator->errors()->add(
                    'email',
                    'Email không hợp lệ !'
                );
            }
        });
    }
}
