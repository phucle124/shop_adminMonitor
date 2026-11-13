<?php

namespace App\Http\Controllers;

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
}
