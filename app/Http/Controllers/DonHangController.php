<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\SanPham;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;

use function Illuminate\Support\now;

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

                // Ghi log đơn hàng
                app(LogTrangThaiController::class)->ghiLogTrangThai(
                    (string)$donhang->madh,
                    'DON_HANG',
                    'CHO_XAC_NHAN',
                    'CHUA_THANH_TOAN',
                );

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


    // Kiểu trả về là đối tượng (kiểu model) cho dạng "môđun xử lý trạng thái Hủy"
    public function syncExpiredOrderStatus(DonHang $donHang): DonHang{

        $donHang->refresh();

        //Mô tả Luồng xử lý "hủy khi hết timer":
        // Chỉ cập nhật hủy nếu 'chưa thanh toán' -> nếu 'chưa bị hủy' -> nếu 'đã có thời điểm hết hạn QR'
        //-> nếu record đơn hàng ở [thời điểm Hiện tại] trùng khớp hoặc sau [thời điểm Hết hạn]
        //Thì cập nhật 'trangthai_donhang' => 'DA_HUY'

        if($donHang->trangthai_thanhtoan !== 'DA_THANH_TOAN'){
            if($donHang->trangthai_donhang !== 'DA_HUY'){
                if(!empty($donHang->qr_expires_at)){

                  //Convert kiểu ngày + giờ cùng loại với những biến khác => TRÁNH LỆCH KIỂU DỮ LIỆU
                    $qrExpires_at = Carbon::parse($donHang->qr_expires_at);

                    if(now()->greaterThanOrEqualTo($qrExpires_at)){
                        $donHang->update(['trangthai_donhang' => 'DA_HUY']);
                        $donHang->refresh();


                        // Ghi log đơn hàng
                        app(LogTrangThaiController::class)->ghiLogTrangThai(
                            (string)$donHang->madh,
                            'DON_HANG',
                            $donHang->trangthai_donhang,
                            $donHang->trangthai_thanhtoan,
                        );
                    }
                }
            }
        }

        
        return $donHang;
    }

    public function triggerTestTransactionCallback(Request $request){
        
        //Lấy mã đơn hàng (số nguyên) từ form gửi lên hoặc từ session tạm
        $madh = (int) ($request->input('madh') ?? $request->input('orderId') ?? session('madh_cho_thanh_toan') ?? 0);

        if ($madh <= 0) {
            return redirect()->back()->with('error', 'Không tìm thấy mã đơn hàng hợp lệ.');
        }

        //Tìm đơn hàng trong cơ sở dữ liệu
        $donHang = DonHang::where('madh', $madh)->first();

        if (!$donHang) {
            return redirect()->back()->with('error', 'Không tìm thấy đơn hàng #' . $madh);
        }

        //Kiểm tra xem đã quá 15 phút chưa -> nếu quá thì tự chuyển đơn sang HUY
        $donHang = $this->syncExpiredOrderStatus($donHang);

        if (($donHang->trangthai_donhang ?? '') === 'DA_HUY') {
            return redirect()->back()->with('error', 'Đơn hàng #' . $donHang->madh . ' đã quá 15 phút nên bị hủy tự động.');
        }

        if (($donHang->trangthai_thanhtoan ?? '') === 'DA_THANH_TOAN') {
            return redirect()->back()->with('success', 'Đơn hàng #' . $donHang->madh . ' đã thanh toán rồi.');
        }

        // Chuẩn bị dữ liệu gửi sang VietQR để giả lập giao dịch chuyển khoản
        $soTien      = (string) (int) round((float) ($request->input('amount') ?? $donHang->tongtien));
        $noiDung     = (string) ($request->input('content'));
        $soTaiKhoan  = (string) $request->input('bankAccount', '');
        $maNganHang  = (string) $request->input('bankCode', '');

        session(['madh_cho_thanh_toan' => $donHang->madh]);

        // Tạo request giả lập và gọi route chứa hàm testTransactionCallback bên GiaoDichQRController
        $qrRequest = Request::create(
            route('vqr.callback.test'),
            'POST',
            [
                'bankAccount' => $soTaiKhoan,
                'bankCode'    => $maNganHang,
                'amount'      => $soTien,
                'content'     => $noiDung,
            ]
        );
        $qrRequest->setLaravelSession($request->session());

        try {
            $qrController = app(GiaoDichQRController::class);
            $qrResponse   = $qrController->testTransactionCallback($qrRequest);

            $ketQua = method_exists($qrResponse, 'getData') ? $qrResponse->getData(true) : [];
            $trangThai = strtoupper((string) ($ketQua['status'] ?? 'FAILED'));

            // Thông báo kết quả quay lại màn hình hiện tại
            if ($trangThai === 'SUCCESS') {

                // Ghi log đơn hàng
                app(LogTrangThaiController::class)->ghiLogTrangThai(
                    session('madh_vua_tao'),
                    'DON_HANG',
                    $donHang->tranthai_donhang,
                    $donHang->trangthai_thanhtoan,
                );

                return redirect()->back()->with('success', 'Đã gửi giả lập thanh toán cho đơn #' . $donHang->madh);
             
               
            } else {
                $loi = (string) ($ketQua['message'] ?? 'Lỗi không xác định');

                

                return redirect()->back()->with('error', 'Giả lập thanh toán thất bại: ' . $loi);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi gọi giả lập thanh toán: ' . $e->getMessage());
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
            'message' => 'Đang chờ thanh toán'
        ]);
    }
}
