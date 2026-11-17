<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KhachHangController;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

//Khách đăng ký
Route::get('register', [AuthController::class,'showRegister']);
Route::post('register', [AuthController::class, 'register'])->name('register.post');

//Khách đăng nhập
Route::get('login', [AuthController::class, 'showLogin_Khach']);
Route::post('login',[AuthController::class, 'login_Khach'])->name('login.post');


//index Khách (Cần thêm middleware Check session('ma_khach_hang'))
Route::get('/KH-index',[KhachHangController::class,'index'])->name('KhachHang.index');


//Product
Route::view('products','KhachHangView.products');

//Quản trị viên đăng nhập
// Route::get('login', [AuthController::class, 'showLogin_Admin']);
// Route::post('login', [AuthController::class, 'login_Admin'])->name('admin_login.post');

//index Quản trị viên
Route::view('index','AdminView.index')->name('Admin.index');

