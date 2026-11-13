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

    public function buyNow(Request $req){
        $req->validate([
            'masp' => 'required|exists:sanpham,masp',
            'soluong_mua' => 'required|integer|min:1',
        ]);

        $sp = SanPham::find($req->masp);
        if(!$sp){
            return redirect()->route('sanpham.details')->with('error','Không tìm thấy sản phẩm !');
        }

        $sl_Mua = $req->soluong_mua;

        if($sl_Mua > $sp->soluong_tonkho)
            return back()->with('error', 'Sản phẩm không đủ') //Dùng 'back()' Thay vi 'redirect()->route('')'
                            ->withInput(); //Giữ lại input khách đã nhập

        //Tổng tiền khách cần thanh toán
        $tongtien = $sp->dongia * $sl_Mua;

        //Nghiệp vụ Transaction trước khi thực hiện giao dịch thực tế
        try{
            $donhang = DB::transaction(function () use ($sp, $sl_Mua, $tongtien){

                //Tạo đơn hàng
                $donhang = DonHang::create([
                    'makh' => Auth::id(),
                    'tongtien' => $tongtien,
                    'trangthai_donhang' => 'CHO_XAC_NHAN',
                    'trangthai_thanhtoan' => 'CHUA_THANH_TOAN',
                ]);

                //Tạo chi tiết đơn hàng
                $donhang->chitiet_donhangs()->create([
                    'giaban' => $sp->dongia,
                    'soluong_mua' => $sl_Mua,
                    'madh' => $donhang->madh,
                    'masp' => $sp->masp,
                ]);
            
                return $donhang;
            });

        }catch(\Exception){
            DB::rollBack();
            return back()->with('error','Lỗi giao dịch !')->withInput();
        }
    }
}
