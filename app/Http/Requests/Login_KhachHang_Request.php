<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class Login_KhachHang_Request extends FormRequest
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
            'identifier' => 'required|string|max:50',
            'matkhau' => 'required|string|min:8|max:100',
        ];
    }

    public function validate_Value(Validator $validator): void{
        $validator->after(function(Validator $validator){
            $identifier = trim($this->input('identifier'));

            //Email
            $validEmail = filter_var($identifier,FILTER_VALIDATE_EMAIL);
            //Số điện thoại
            $validPhone = preg_match('/[0|\+84][0,9]{9,10}$/',$identifier);

            if(!$validEmail || !$validPhone){
                $validator->errors()->add(
                    'identifier',
                    'Email hoặc Số điện thoại không hợp lệ',
                );
            }
            

        });
    }
}
