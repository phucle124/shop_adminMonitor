<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\GiaoDichQR;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GiaoDichQRController extends Controller
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
    public function show(GiaoDichQR $giaoDichQR)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GiaoDichQR $giaoDichQR)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GiaoDichQR $giaoDichQR)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GiaoDichQR $giaoDichQR)
    {
        //
    }

    public function handleVietQRWebhook(Request $request)
    {
        

        // Lấy dữ liệu JSON mà VietQR gửi sang
        $data = $request->all();

       
        Log::info('VietQR Webhook Data:', $data);

        try {
            // Ánh xạ các trường dữ liệu dựa theo cấu trúc payload bạn đã cung cấp
            $maTraCuuQr = $data['qrCode'] ?? null;          // ma_tra_cuu_qr    (giaodichqr)
            $soTien     = $data['amount'] ?? 0;             // sotien           (giaodichqr)
            $noiDung    = $data['content'] ?? '';           // noi_dung_ck      (giaodichqr)
            $stk        = $data['bankAccount'] ?? '';       // stk_ngan_hang    (giaodichqr)
            $tenNganHang= $data['bankCode'] ?? '';          // ten_ngan_hang    (giaodichqr)
            $orderId    = $data['orderId'] ?? null;         // madh         (FK - giaodichqr)

            // Kiểm tra bắt buộc
            if (!$orderId || !$soTien) {
                Log::warning('Webhook: Thiếu orderId hoặc amount.');
                return response()->json(['success' => false, 'message' => 'Missing fields'], 400);
            }

            // 3. Tìm đơn hàng trong Database
            $donHang = DonHang::with('chitiet_donhangs')->where('madh', $orderId)->first();

            if (!$donHang) {
                Log::warning("Webhook: Không tìm thấy đơn hàng mã số #{$orderId}");
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }

            // Kiểm tra xem đơn hàng đã được thanh toán từ trước chưa (Tránh webhook gọi trùng lặp)
            if ($donHang->trangthai_thanhtoan === 'DA_THANH_TOAN') {
                return response()->json(['success' => true, 'message' => 'Order paid']);
            }

            // Kiểm tra số tiền chuyển khoản có = tổng tiền đơn hàng yêu cầu không [Theo nghiệp vụ VNPay về kiểm tra Đơn hàng]
            
            if ((float)$soTien < (float)$donHang->tongtien) {
                Log::warning("Số tiền thanh toán cụ thể là ({$soTien}), ít hơn tổng tiền yêu cầu là ({$donHang->tongtien}) của đơn hàng {$orderId}");
                
                // Lưu lại record FAILED 
                GiaoDichQr::create([
                    'ma_tra_cuu_qr' => $maTraCuuQr ?? null, //qrCode
                    'sotien' => $soTien,
                    'noi_dung_ck' => $noiDung,
                    'stk_ngan_hang' => $stk,
                    'ten_ngan_hang' => $tenNganHang,
                    'trang_thai_qr' => 'FAILED',
                    'madh' => $donHang->madh,
                ]);

                return response()->json(['success' => true, 'message' => 'Amount mismatch recorded']);
            }

            
            DB::transaction(function () use ($donHang, $maTraCuuQr, $soTien, $noiDung, $stk, $tenNganHang) {
              
                $donHang->update([
                    'trangthai_donhang' => 'DA_XAC_NHAN',   // Đơn hàng chính thức được xác nhận
                    'trangthai_thanhtoan' => 'DA_THANH_TOAN', // Đã hoàn tất thanh toán
                ]);

               
                foreach ($donHang->chitiet_donhangs as $chiTiet) {
                    $sanPham = SanPham::lockForUpdate()->find($chiTiet->masp);
                    if ($sanPham) {
                        $sanPham->soluong_tonkho -= $chiTiet->soluong_mua;  //Trừ tồn kho
                        $sanPham->save();
                    }
                }

                // Ghi lại record đã ck thành công vào bảng giaodichqr
                GiaoDichQr::create([
                    'ma_tra_cuu_qr' => $maTraCuuQr,
                    'sotien' => $soTien,
                    'noi_dung_ck' => $noiDung,
                    'stk_ngan_hang' => $stk,
                    'ten_ngan_hang' => $tenNganHang,
                    'trang_thai_qr' => 'SUCCESS',
                    'madh' => $donHang->madh,
                ]);
            });

            Log::info("Webhook: Thanh toán thành công cho đơn hàng #{$orderId}");

            // *BẮT BUỘC => Trả về thông báo cho VietQR biết hệ thống đã nhận và xử lý thành công
            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Lỗi xử lý VietQR Webhook: ' . $e->getMessage());
            // Vẫn trả về 200 hoặc mã lỗi tùy tài liệu VietQR yêu cầu, 
            // nhưng thường nên trả 500 nếu lỗi hệ thống để bên VietQR biết và push lại sau.
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
