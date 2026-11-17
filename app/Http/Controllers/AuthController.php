<?php

namespace App\Http\Controllers;

use App\Http\Requests\Login_Admin_Request;
use App\Http\Requests\Login_KhachHang_Request;
use App\Http\Requests\RegisterRequest;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister(){
        return view('authentications.register');
    }

    public function register(RegisterRequest $registerReq){


        $identifier = trim($registerReq->identifier);

        $email = null;
        $sdt = null;


        if(filter_var($identifier,FILTER_VALIDATE_EMAIL))
            $email = $identifier;
        else
            $sdt = $identifier;


        KhachHang::create([
            'hoten' => $registerReq->get('hoten'),
            // 'email' => $registerReq->get('email'),
            'email' => $email,
            'sdt' => $sdt,
            'matkhau' => Hash::make($registerReq->get('matkhau')),

        ]);

        return back()->with('auth_message','Đăng ký thành công!');
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

    
    public function login_Admin(Login_Admin_Request $loginReq){
        $email = trim($loginReq->email);

        $Admin_Info = KhachHang::where('khachhang','email')->first();

        if( !$Admin_Info || !Hash::check($loginReq->matkhau, $Admin_Info->matkhau)){
            return back()->withInput($loginReq->only('identifier'))->with('auth_message','Sai thông tin đăng nhập hoặc mật khẩu !');
        }

         //Tạo 1 session ở phía client

        $loginReq->session()->regenerate();

        session([
            'ma_khach_hang' => $Admin_Info->maqt,
            
        ]);

        //Chuyển tiếp tới trang mặc định của Admin

        return redirect()->route('Admin.index');
    }
}
