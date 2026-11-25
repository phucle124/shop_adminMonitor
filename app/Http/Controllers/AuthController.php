<?php

namespace App\Http\Controllers;

use App\Services\ProvinceService;
use App\Http\Requests\Login_Admin_Request;
use App\Http\Requests\Login_KhachHang_Request;
use App\Http\Requests\RegisterRequest;
use App\Models\KhachHang;
use App\Models\QuanTriVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showRegister(){
        $provinceService =  app(ProvinceService::class);
        $provinces = $provinceService->getAllProvinces();
        return view('authentications.register', compact('provinces'));
    }

    public function register(Request $request, ProvinceService $provinceService){

        $vnPhoneRegex = '/^(096|097|098|03[2-9]|086|090|093|070|07[6-9]|089|091|094|08[1-5]|088|092|052|056|058|099|059|087)\d{7}$/';

        $validator = Validator::make($request->all(), [
            'hoten'         => ['required', 'string', 'max:120'],
            'identifier'    => ['required', 'string', 'max:150'],
            'matkhau'       => ['required', 'string', 'min:6'],
            'gioitinh'      => ['required', 'in:NAM,NU,khac'],
            'province_code' => ['required', 'integer'],
        ], [
            'hoten.required'         => 'Vui lòng nhập họ và tên.',
            'identifier.required'    => 'Vui lòng nhập email hoặc số điện thoại.',
            'matkhau.required'       => 'Vui lòng nhập mật khẩu.',
            'matkhau.min'           => 'Mật khẩu tối thiểu 6 ký tự.',
            'gioitinh.required'      => 'Vui lòng chọn giới tính.',
            'gioitinh.in'            => 'Giới tính không hợp lệ.',
            'province_code.required' => 'Vui lòng chọn tỉnh/thành phố.',
        ]);

        $validator->after(function ($v) use ($request, $vnPhoneRegex, $provinceService) {
            $identifier = trim((string) $request->input('identifier', ''));
            $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

            if (!$isEmail && !preg_match($vnPhoneRegex, $identifier)) {
                $v->errors()->add(
                    'identifier',
                    'Vui lòng nhập email hợp lệ hoặc số điện thoại Việt Nam hợp lệ (Viettel, Mobifone, Vinaphone, Vietnamobile, Gmobile, Itelecom).'
                );
            }

            $provinceCode = (int) $request->input('province_code');
            if ($provinceCode > 0 && !$provinceService->findProvinceByCode($provinceCode)) {
                $v->errors()->add('province_code', 'Tỉnh/thành phố đã chọn không hợp lệ.');
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('matkhau'))
                ->with('auth_message', $validator->errors()->first());
        }

        $identifier = trim((string) $request->input('identifier'));
        $plainPassword = (string) $request->input('matkhau');
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        $email = $isEmail ? mb_strtolower($identifier) : null;
        $sdt   = $isEmail ? null : $identifier;

        // Kiểm tra tài khoản đã tồn tại theo email hoặc sđt
        $existingUser = KhachHang::when($email, fn ($q) => $q->where('email', $email))
            ->when($sdt, fn ($q) => $q->orWhere('sdt', $sdt))
            ->first();

        if ($existingUser) {
            // Kiểm tra xem có trùng đúng cụm [email/sđt + mật khẩu] không
            if (Hash::check($plainPassword, $existingUser->matkhau)) {
                return back()
                    ->withInput($request->except('matkhau'))
                    ->with('auth_message', 'Tài khoản với thông tin và mật khẩu này đã tồn tại. Vui lòng đăng nhập!');
            }

            return back()
                ->withInput($request->except('matkhau'))
                ->with('auth_message', $isEmail
                    ? 'Email này đã được đăng ký bởi một tài khoản khác.'
                    : 'Số điện thoại này đã được đăng ký bởi một tài khoản khác.');
        }

        $province = $provinceService->findProvinceByCode((int) $request->input('province_code'));

        $khachHang = KhachHang::create([
            'hoten'         => trim((string) $request->input('hoten')),
            'email'         => $email,
            'sdt'           => $sdt,
            'matkhau'       => Hash::make($plainPassword),
            'gioitinh'      => (string) $request->input('gioitinh'),
            'province_code' => $province['code'] ?? null,
            'province_name' => $province['name'] ?? null,
        ]);

        $request->session()->regenerate();
        session([
            'ma_khach_hang'  => $khachHang->makh,
            'ten_khach_hang' => $khachHang->hoten,
        ]);

        return redirect()->route('KhachHang.index')
            ->with('auth_message', 'Đăng ký thành công!');
    }

    public function showLogin_Khach(){
        return view('authentications.loginKhachHang');
    }

    public function login_Khach(Login_KhachHang_Request $loginReq){
        $identifier = trim($loginReq->identifier);

        if(filter_var($identifier, FILTER_VALIDATE_EMAIL))
            $KH_Info = KhachHang::where('email',$identifier)->first();
        else 
            $KH_Info = KhachHang::where('sdt',$identifier)->first();

        if( !$KH_Info || !Hash::check($loginReq->matkhau, $KH_Info->matkhau)){
            return back()->withInput($loginReq->only('identifier'))->with('auth_message','Sai thông tin đăng nhập hoặc mật khẩu !');
        }

        $ten_avatar = $KH_Info->hoten;

        //Tạo 1 session ở phía client

        $loginReq->session()->regenerate();

        session([
            'ma_khach_hang' => $KH_Info->makh,
            'ten_khach_hang' => $KH_Info->hoten,
        ]);

        //Chuyển tiếp tới trang mặc định khi Khách hàng đăng nhập/đăng ký thành công
        
        return view('KhachHangView.index',compact('ten_avatar'));

    }

    
    

    public function logout(Request $request){

        $request->session()->forget([
            'ma_khach_hang',
            'ten_khach_hang',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('KhachHang.index') 
            ->with('auth_message', 'Đã đăng xuất.');
    }

    public function showLogin_Admin()
    {
        if (session()->has('admin_id')) {
            return redirect()->route('Admin.index');
        }

        return view('authentications.loginAdmin');
    }

    public function login_Admin(Request $request)
    {
        $credentials = $request->validate([
            'email'   => ['required', 'email'],
            'matkhau' => ['required', 'string'],
        ], [
            'email.required'   => 'Vui lòng nhập email quản trị viên.',
            'email.email'      => 'Định dạng email không hợp lệ.',
            'matkhau.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $admin = QuanTriVien::where('email',$credentials['email'])->first();

        if (!$admin || !Hash::check($credentials['matkhau'], $admin->matkhau)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Email hoặc mật khẩu quản trị viên không đúng.');
        }

        $request->session()->regenerate();

        session([
            'admin_id'    => $admin->maqt,
            'admin_ten'   => $admin->hoten,
            'admin_email' => $admin->email,
        ]);

        return redirect()
            ->route('Admin.index')
            ->with('success', 'Đăng nhập quản trị viên thành công.');
    }

    public function logout_Admin(Request $request){

        $request->session()->forget([
            'admin_id',
            'admin_ten',
            'admin_email',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('admin.login')
            ->with('success', 'Đã đăng xuất khỏi trang quản trị.');
    }
}
