<?php

namespace App\Http\Controllers;

use App\Models\SanPham;
use Illuminate\Http\Request;

class SanPhamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Getall Data + Trả về View Giao diện sanpham.index

        $ten_avatar = session('ten_khach_hang');
        return view('KhachHangView.products',compact('ten_avatar'));
        
    }

    public function indexNormal()
    {
        //Dùng tạm eager loading để test
        $dssp1 = SanPham::where('loaisp', 'NORMAL')->get();
        
        $ten_avatar = session('ten_khach_hang');

        return view('KhachHangView.productNormal', compact('dssp1', 'ten_avatar'));
    }

    public function indexSpecial()
    {
        //Dùng tạm eager loading để test
        $dssp2 = SanPham::where('loaisp', 'SPECIAL')->get();
       
        $ten_avatar = session('ten_khach_hang');

        return view('KhachHangView.productSpecial', compact('dssp2', 'ten_avatar'));
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
    public function show($masp)
    {
        //Get data by id

        $sp = SanPham::where('masp',$masp)->first();
        if(!$sp) return redirect()->back()->with('error','Sản phẩm không tồn tại!');
        

        $ten_avatar = session('ten_khach_hang');

        return view('KhachHangView.productDetails',compact('sp','ten_avatar'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SanPham $sanPham)
    {
        //Trả về View Giao diện edit
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SanPham $sanPham)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SanPham $sanPham)
    {
        //
    }
}
