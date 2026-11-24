<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\SanPham;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;

class DonHangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(DonHang $donHang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DonHang $donHang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DonHang $donHang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DonHang $donHang)
    {
        //
    }

    public function buyNow(Request $req)
{
    $req->validate([
        'masp' => 'required|exists:sanpham,masp',
    ]);

    $sp = SanPham::find($req->masp);

    if (!$sp) {
        return redirect()->route('sanpham.show')->with('error', 'Không tìm thấy sản phẩm !');
    }

    $req->validate([
        'soluong_mua' => 'required|integer|min:1|max:' . $sp->soluong_tonkho,
    ], [
        'soluong_mua.max' => '!!![CẢNH BÁO]: không được mua vượt quá số lượng tồn kho là (' . $sp->soluong_tonkho . ') cái!!!'
    ]);

    $maKhachHang = $req->input('makh') ?? session('ma_khach_hang') ?? Auth::id();
    if (!$maKhachHang) {
        return redirect()->route('login')->with('error', 'Vui lòng đăng nhập trước khi đặt hàng!');
    }

    $sl_Mua = (int) $req->soluong_mua;

    if ($sl_Mua > $sp->soluong_tonkho) {
        return back()->with('error', 'Sản phẩm không đủ')->withInput();
    }

    // Tổng tiền khách cần thanh toán (không voucher/khuyến mãi -> giaban = dongia)
    $tongtien = $sp->dongia * $sl_Mua;

    try {
        $donhang = DB::transaction(function () use ($maKhachHang, $sp, $sl_Mua, $tongtien) {
            $donhang = DonHang::create([
                'makh'                => $maKhachHang,
                'tongtien'            => $tongtien,
                'trangthai_donhang'   => 'CHO_XAC_NHAN',
                'trangthai_thanhtoan' => 'CHUA_THANH_TOAN',
            ]);

            $donhang->chitiet_donhangs()->create([
                'giaban'      => $sp->dongia,
                'soluong_mua' => $sl_Mua,
                'madh'        => $donhang->madh,
                'masp'        => $sp->masp,
            ]);

            return $donhang;
        });

        // Lưu madh vừa tạo vào session để caller nội bộ lấy được ngay cả khi return redirect
        session(['madh_vua_tao' => $donhang->madh]);


        //PHẢI TRẢ mã đơn hàng mã phía
        return redirect()->route('donhang.confirm', ['madh' => $donhang->madh])->with('success', 'Tạo đơn hàng thành công!');
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Lỗi buyNow: ' . $e->getMessage());
        return back()->with('error', 'Lỗi giao dịch !')->withInput();
    }
}

    public function checkPaymentStatus($madh){
        $donHang = DonHang::where('madh', $madh)->first();

        if (!$donHang) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

   
        if ($donHang->trangthai_thanhtoan == 'DA_THANH_TOAN') {
            return response()->json([
                'success' => true,
                'is_paid' => true,
                'message' => 'Thanh toán thành công!'
            ]);
        }

        return response()->json([
            'success' => true,
            'is_paid' => false,
            'message' => 'Đang chờ thanh toán...'
        ]);
    }
}
