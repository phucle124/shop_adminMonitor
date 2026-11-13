<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hoten' => 'required|string|max:50',
            'identifier' => 'required|string|max:50',
            'matkhau' => 'required|string|min:8|max:100',
        ];
    }

    public function validate_Value(Validator $validator): void{
        $validator->after(function ($validator){

            //Luồng 1:Hứng input 'identifier' từ client
            $identifier = trim($this->input('identifier'));

            //Luồng 2: xử lý phân biệt email hoặc sđt
            //Email
            if(filter_var($identifier,FILTER_VALIDATE_EMAIL)){
                $validator->sometimes('identifier',['email',Rule::unique('khachhang','email')], fn() => true);
            }

            //Số điện thoại với Regex +84xxxxxxxxxx với các kí tự từ 0 -> 9
            elseif(preg_match('/^(0|\+84)[0-9]{9,10}$/',$identifier)){
                $validator->sometimes('identifier',[Rule::unique('khachhang','sdt')], fn() => true);
            
            }
            
        });
    }
}
