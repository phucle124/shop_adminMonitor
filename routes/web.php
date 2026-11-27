<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DonHangController;
use App\Http\Controllers\GiaoDichQRController;
use App\Http\Controllers\KhachHangController;
use App\Http\Controllers\Admin\LogExportController;
use App\Http\Controllers\LogTrangThaiController;
use App\Http\Controllers\SanPhamController;
use App\Http\Controllers\VietQrAuthController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/settings.php';

// Khách đăng ký
Route::get('register', [AuthController::class, 'showRegister'])->name('register');
Route::post('register', [AuthController::class, 'register'])->name('register.post');

// Khách đăng nhập
Route::get('login', [AuthController::class, 'showLogin_Khach'])->name('login');
Route::post('login', [AuthController::class, 'login_Khach'])->name('login.post');


// Trang index
Route::get('/', [KhachHangController::class, 'index'])->name('KhachHang.index');

// Product
Route::get('/products', [SanPhamController::class, 'index'])->name('sanphamIntroduce.index');
Route::get('/productNormal', [SanPhamController::class, 'indexNormal'])->name('sanpham1.index');
Route::get('/productSpecial', [SanPhamController::class, 'indexSpecial'])->name('sanpham2.index');
Route::get('/productDetails/{masp}', [SanPhamController::class, 'show'])->name('sanpham.show');

// Đơn hàng
// Route::post('/khach-hang/tao-donhang-tmp', [KhachHangController::class, 'DonHangTmp'])->name('donhangtmp.post');
// Route::get('/khach-hang/xem-lai-don-hang', [KhachHangController::class, 'xemLaiDonHang'])->name('donhangtmp.preview');
// Route::post('/khach-hang/xac-nhan-don-hang', [KhachHangController::class, 'xacNhanDatHang'])->name('donhang.confirm');
// Route::post('/khach-hang/huy-don-hang', [KhachHangController::class, 'HuyDatHang'])->name('donhangtmp.cancel');
// Route::get('/khach-hang/don-hang/{madh}/trang-thai', [KhachHangController::class, 'kiemTraTrangThaiDonHang'])->name('khachhang.donhang.trang-thai');


// Bắt buộc đăng nhập với toàn bộ phần đơn hàng và đăng xuất
Route::middleware('khach.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('KhachHang.logout');

    // Đơn hàng
    Route::post('/khach-hang/tao-donhang-tmp', [KhachHangController::class, 'DonHangTmp'])->name('donhangtmp.post');
    Route::get('/khach-hang/xem-lai-don-hang', [KhachHangController::class, 'xemLaiDonHang'])->name('donhangtmp.preview');
    Route::post('/khach-hang/xac-nhan-don-hang', [KhachHangController::class, 'xacNhanDatHang'])->name('donhang.confirm');
    Route::post('/khach-hang/huy-don-hang', [KhachHangController::class, 'HuyDatHang'])->name('donhangtmp.cancel');
    Route::get('/khach-hang/don-hang/{madh}/trang-thai', [KhachHangController::class, 'kiemTraTrangThaiDonHang'])->name('khachhang.donhang.trang-thai');
    Route::post('/don-hang/create', [DonHangController::class, 'buyNow'])->name('donhang.buy-now');
});

Route::prefix('vqr')->group(function () {
    Route::post('/api/token_generate', [VietQrAuthController::class, 'tokenGenerate'])->name('vqr.token.generate');
    Route::post('/api/qr/generate-customer', [VietQrAuthController::class, 'getCustomerQr'])->name('vqr.customer.generate');
    Route::post('/bank/api/transaction-sync', [GiaoDichQRController::class, 'transactionSync'])->name('vqr.transaction.sync');
    Route::post('/bank/api/test/transaction-callback', [GiaoDichQRController::class, 'testTransactionCallback'])->name('vqr.callback.test');
});

// Trang giám sát / Quản trị viên
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin_Admin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login_Admin'])->name('admin.login.post');
    Route::post('/logout', [AuthController::class, 'logout_Admin'])->name('admin.logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [LogTrangThaiController::class, 'adminIndex'])->name('Admin.index');
        Route::get('/index', [LogTrangThaiController::class, 'adminIndex']);
        Route::get('/logs/export', [LogExportController::class, 'export'])->name('logs.export');
    });
});