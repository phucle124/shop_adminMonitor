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
        //Validate Tĩnh => Đảm bảo dữ liệu bắt buộc đúng ở mức tối thiểu khi vào input
           //  Đặc biệt => Tránh việc xây dựng quy tắc cồng kềnh, phức tạp ở hàm này
        return [
            'hoten' => 'required|string|max:50',
            'identifier' => 'required|string|max:50',
            'matkhau' => 'required|string|min:8|max:100',
        ];
    }


    /* Validate Động => Chỉ chạy sau khi dữ liệu đã đáp ứng được ở hàm rules()
                    => Xử lý các logic phức tạp, phụ thuộc vào ngữ cảnh 
                    hoặc giá trị Động
                    
                    => Xử lý các bài toán kiểm tra chéo (cross-field validation) 
                    hoặc tương tác linh hoạt với Database mà 
                    rules() tĩnh không đáp ứng được
    */
    public function validate_Value(Validator $validator): void{     
        $validator->after(function ($validator){

            //Luồng 1:Hứng input 'identifier' từ client
            $identifier = trim($this->input('identifier'));

            //Luồng 2: xử lý phân biệt email hoặc sđt
            //Email
            //Kiểm tra tính duy nhất của cột 'email' ở table khachhang(trong Rule:unique...)
            //'email' (Bên Trái) chính là option CÓ SẴN trong laravel => PHẢI học thuộc để dùng đúng
            if(filter_var($identifier,FILTER_VALIDATE_EMAIL)){
                $validator->sometimes('identifier',['email',Rule::unique('khachhang','email')], fn() => true);
            }


            //Số điện thoại với format +84xxxxxxxxx hoặc 0xxxxxxxxx 
            // gồm các kí tự từ 0->9 có độ dài từ 9->10 kí tự
            // mới có quyền đổ vào Database
            elseif(preg_match('/^(0|\+84)[0-9]{9,10}$/',$identifier)){
                $validator->sometimes('identifier',[Rule::unique('khachhang','sdt')], fn() => true);
            
            }
            
        });
    }
}
