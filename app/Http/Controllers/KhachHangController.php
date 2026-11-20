<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KhachHangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $ten_avatar = session('ten_khach_hang');
        return view('KhachHangView.index',compact('ten_avatar'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(KhachHang $khachHang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KhachHang $khachHang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KhachHang $khachHang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KhachHang $khachHang)
    {
        //
    }

    public function DonHangTmp(Request $request)
    {
        // Lấy thông tin khách hàng đang lưu trong session hiện tại
        $ma_khach_hang = session('ma_khach_hang'); 
        $ten_khach_hang = session('ten_khach_hang');

        // Kiểm tra xem khách đã đăng nhập/có session thông tin chưa
        if (!$ma_khach_hang) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập trước khi mua hàng!');
        }

        // Gom dữ liệu tạm thời
        $donHangTam = [
            'khach_hang' => [
                'ma_khach_hang' => $ma_khach_hang,
                'ten_khach_hang' => $ten_khach_hang,
            ],
            'san_pham' => [
                'masp' => $request->masp,
                'tensp' => $request->tensp,
                'dongia' => $request->dongia,
                'hinh' => $request->hinh,
                'soluong_mua' => $request->soluong_mua,
                'tongtien' => $request->dongia * $request->soluong_mua,
            ]
        ];

        // Lưu vào Session tạm
        session(['don_hang_tam' => $donHangTam]);

        // Chuyển hướng sang trang xem lại đơn hàng (Checkout Preview)
        return redirect()->route('donhangtmp.preview');
    }

    public function xemLaiDonHang()
    {
        $donhang = session('don_hang_tam');
        if (!$donhang) {
            return redirect()->back()->with('error', 'Không có đơn hàng nào chờ xử lý.');
        }

        return view('KhachHangView.donhangPreview', compact('donhang'));
    }

    //Hàm chính thức lưu vào Database khi khách bấm "Xác nhận đặt hàng"
    public function xacNhanDatHang($madh)
    {
        //

        $donhang = DonHang::with(['chitiet_donhangs.sanpham', 'khachhang'])->where('madh', $madh)->firstOrFail();

        // Kiểm tra xem đơn hàng có đúng của khách hàng đang đăng nhập hay không (bảo mật cơ bản)
        if ($donhang->makh != Auth::makh()) {
            return redirect()->route('home')->with('error', 'Bạn không có quyền xem đơn hàng này!');
        }

        // Trả về view hiển thị trang xác nhận đơn hàng / quét mã QR thanh toán
        return view('khachhang.xacnhan-donhang', compact('donhang'));
    }

    public function HuyDatHang()
    {
        //
    }

}
