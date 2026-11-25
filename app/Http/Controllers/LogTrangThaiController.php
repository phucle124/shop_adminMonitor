<?php

namespace App\Http\Controllers;

use App\Models\LogTrangThai;
use Illuminate\Http\Request;

class LogTrangThaiController extends Controller
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
    public function show(LogTrangThai $logTrangThai)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LogTrangThai $logTrangThai)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LogTrangThai $logTrangThai)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LogTrangThai $logTrangThai)
    {
        //
    }

    public function ghiLogTrangThai(
        string $maDoiTuong,
        string $loaiDoiTuong = 'DON_HANG',
        ?string $trangThaiDh = null,
        ?string $trangThaiTt = null
    ): void {
        LogTrangThai::create([
            'ma_doituong'  => $maDoiTuong,
            'loai_doituong' => $loaiDoiTuong,
            'trangthai_dh' => $trangThaiDh,
            'trangthai_tt' => $trangThaiTt,
        ]);
    }

    public function adminIndex(Request $request){
        
        $loai = $request->input('loai');
        $ma   = trim((string) $request->input('ma', ''));

        $logs = LogTrangThai::query()
            ->when(in_array($loai, ['DON_HANG', 'GIAO_DICH_QR'], true), function ($q) use ($loai) {
                $q->where('loai_doituong', $loai);
            })
            ->when($ma !== '', function ($q) use ($ma) {
                $q->where('ma_doituong', 'like', '%' . $ma . '%');
            })
            ->orderByDesc('created_at')
            ->orderByDesc('malog_tt')
            ->paginate(20)
            ->withQueryString();

        $tongLog      = LogTrangThai::count();
        $tongDonHang  = LogTrangThai::where('loai_doituong', 'DON_HANG')->count();
        $tongGiaoDich = LogTrangThai::where('loai_doituong', 'GIAO_DICH_QR')->count();

        return view('AdminView.index', compact(
            'logs',
            'loai',
            'ma',
            'tongLog',
            'tongDonHang',
            'tongGiaoDich'
        ));
    }
}
