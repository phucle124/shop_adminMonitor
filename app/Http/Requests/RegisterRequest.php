<?php

namespace App\Http\Requests;

use App\Models\KhachHang;
use App\Services\ProvinceService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{

    public const VN_PHONE_REGEX = '/^(096|097|098|03[2-9]|086|090|093|070|07[6-9]|089|091|094|08[1-5]|088|092|052|056|058|099|059|087)\d{7}$/';
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
            'gioitinh'      => ['required', 'in:NAM,NU'],
            'province_code' => ['required', 'integer'],
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
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $identifier = trim((string) $this->input('identifier', ''));
            $matkhau    = (string) $this->input('matkhau', '');
            $isEmail    = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
            $isVnPhone  = preg_match(self::VN_PHONE_REGEX, $identifier) === 1;

            // 1. Kiểm tra định dạng email hoặc SĐT nhà mạng VN
            if (!$isEmail && !$isVnPhone) {
                $validator->errors()->add(
                    'identifier',
                    'Vui lòng nhập email hợp lệ hoặc số điện thoại Việt Nam hợp lệ (Viettel, Mobifone, Vinaphone, Vietnamobile, Gmobile, Itelecom).'
                );
                return;
            }

            // 2. Kiểm tra tài khoản đã tồn tại & trùng cụm [email/sđt + mật khẩu]
            $normalizedEmail = $isEmail ? mb_strtolower($identifier) : null;
            $normalizedPhone = $isVnPhone ? $identifier : null;

            $existingUser = KhachHang::query()
                ->when($normalizedEmail, fn ($q) => $q->where('email', $normalizedEmail))
                ->when($normalizedPhone, fn ($q) => $q->orWhere('sdt', $normalizedPhone))
                ->first();

            if ($existingUser) {
                if (!empty($existingUser->matkhau) && Hash::check($matkhau, $existingUser->matkhau)) {
                    $validator->errors()->add(
                        'identifier',
                        'Tài khoản với thông tin và mật khẩu này đã tồn tại. Vui lòng đăng nhập!'
                    );
                } else {
                    $validator->errors()->add(
                        'identifier',
                        $isEmail
                            ? 'Email này đã được đăng ký bởi một tài khoản khác.'
                            : 'Số điện thoại này đã được đăng ký bởi một tài khoản khác.'
                    );
                }
            }

            // 3. Kiểm tra province_code nằm trong danh sách tỉnh v2
            $provinceCode = (int) $this->input('province_code');
            $provinceService = app(ProvinceService::class);
            if (!$provinceService->findProvinceByCode($provinceCode)) {
                $validator->errors()->add('province_code', 'Tỉnh/thành phố không tồn tại trong hệ thống.');
            }
        });
    }
}
