<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;

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
    // public function create()
    // {
    //     //
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     //
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(KhachHang $khachHang)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(KhachHang $khachHang)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, KhachHang $khachHang)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(KhachHang $khachHang)
    // {
    //     //
    // }

    public function DonHangTmp(Request $request){

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

    public function xemLaiDonHang(){

        $donhang = session('don_hang_tam');
        if (!$donhang) {
            return redirect()->back()->with('error', 'Không có đơn hàng nào chờ xử lý.');
        }

        return view('KhachHangView.donhangTmp_Preview', compact('donhang'));
    }



    //Hàm chính thức lưu vào Database khi khách bấm "Xác nhận đặt hàng"
    public function xacNhanDatHang(Request $request){

            $maKhachHang = session('ma_khach_hang');
        if (!$maKhachHang) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập trước khi xác nhận đặt hàng!');
        }

        $donhangtmp = session('don_hang_tam');
        if (!$donhangtmp || empty($donhangtmp['khach_hang']) || empty($donhangtmp['san_pham'])) {
            return redirect()->route('home')->with('error', 'Không tìm thấy dữ liệu đơn hàng tạm trong phiên làm việc!');
        }

        $donhangtmp_KH = $donhangtmp['khach_hang'];
        $donhangtmp_SP = $donhangtmp['san_pham'];

        $maKhDonTam = $donhangtmp_KH['ma_khach_hang'] ?? $donhangtmp_KH['makh'] ?? null;
        if ((string) $maKhDonTam !== (string) $maKhachHang) {
            return redirect()->route('KhachHang.index')->with('error', 'Nguời dùng không hợp lệ. Bạn không có quyền xác nhận đơn hàng này!');
        }

        $masp       = (int) ($donhangtmp_SP['masp']);
        $soLuongMua = (int) ($donhangtmp_SP['soluong_mua']);
        $donGia     = (float) ($donhangtmp_SP['dongia']);
        $tongTien   = isset($donhangtmp_SP['tongtien']) ? (float) $donhangtmp_SP['tongtien'] : ($donGia * $soLuongMua);

        if ($masp <= 0 || $donGia <= 0 || $tongTien <= 0) {
            return redirect()->back()->with('error', 'Dữ liệu sản phẩm hoặc tổng tiền không hợp lệ.');
        }

        // Dựng Request nội bộ khớp input mà DonHangController@buyNow mong đợi
        $buyNowRequest = Request::create(route('donhang.buy-now'), // đổi sang tên route thật của hàm buyNow nếu khác
            'POST',
            [
                'makh'        => (int) $maKhachHang,
                'masp'        => $masp,
                'soluong_mua' => $soLuongMua,
                'dongia'      => $donGia,
                'giaban'      => $donGia, // không voucher/khuyến mãi -> giá bán bằng đơn giá
                'tongtien'    => $tongTien,
            ]
        );
        $buyNowRequest->setLaravelSession($request->session());

        try {
            /** @var DonHangController $donHangController */
            $donHangController = app(DonHangController::class);

            //Đang Listen phía Môđun buyNow chủ động trả thẳng (redirect) về cái route đã neo với hàm xacNhanDatHang
            $ketQuaBuyNow      = $donHangController->buyNow($buyNowRequest);

            // Nếu buyNow trả về JsonResponse hoặc mảng/model, bóc dữ liệu linh hoạt
            $donHang = null;

        if ($ketQuaBuyNow instanceof DonHang) {
            $donHang = $ketQuaBuyNow;
        } elseif ($ketQuaBuyNow instanceof JsonResponse) {
            $payload = $ketQuaBuyNow->getData(true);
            if ($ketQuaBuyNow->getStatusCode() >= 400 || ($payload['error'] ?? false)) {
                return redirect()->back()->with('error', $payload['message'] ?? 'Không thể khởi tạo đơn hàng từ buyNow.');
            }

            $madhTaoMoi = data_get($payload, 'madh')
                ?? data_get($payload, 'data.madh')
                ?? data_get($payload, 'donhang.madh')
                ?? session()->pull('madh_vua_tao');

            if ($madhTaoMoi) {
                $donHang = DonHang::where('madh', $madhTaoMoi)->first();
            }
        } elseif ($ketQuaBuyNow instanceof RedirectResponse) {
            $redirectSession = $ketQuaBuyNow->getSession() ?? $request->session();
            $flashError      = $redirectSession->get('error');
            $sessionErrors   = $redirectSession->get('errors');

            if ($flashError) {
                return redirect()->back()->with('error', $flashError);
            }
            if ($sessionErrors && $sessionErrors->any()) {
                return redirect()->back()->withErrors($sessionErrors);
            }

            // Lấy madh từ session (do buyNow lưu) hoặc từ query ?madh= trên targetUrl
            $madhTuSession = $redirectSession->pull('madh_vua_tao') ?? session()->pull('madh_vua_tao');
            parse_str(parse_url($ketQuaBuyNow->getTargetUrl(), PHP_URL_QUERY) ?? '', $queryParams);
            $madhTarget = $madhTuSession ?? ($queryParams['madh'] ?? null);

            if ($madhTarget) {
                $donHang = DonHang::where('madh', $madhTarget)->first();
            }
        } elseif (is_array($ketQuaBuyNow)) {
            $donHang = $ketQuaBuyNow['donHang'] ?? $ketQuaBuyNow['donhang'] ?? null;
        }

        // Fallback: lấy đơn mới nhất của khách hàng nếu vẫn chưa bắt được madh
        if (!$donHang) {
            $donHang = DonHang::where('makh', (int) $maKhachHang)
                ->orderByDesc('madh')
                ->first();
        }

        if (!$donHang) {
            Log::warning('Không lấy được thông tin đơn hàng mới sau buyNow', [
                'makh'            => $maKhachHang,
                'ketQuaBuyNow'    => is_object($ketQuaBuyNow) ? get_class($ketQuaBuyNow) : gettype($ketQuaBuyNow),
                'targetUrl'       => $ketQuaBuyNow instanceof RedirectResponse ? $ketQuaBuyNow->getTargetUrl() : null,
                'session_madh'    => session('madh_vua_tao'),
            ]);

            return redirect()->back()->with('error', 'Đơn hàng đã được xử lý nhưng không lấy được thông tin đơn mới');
        }
            $qrInfo = null;
            $qrLink = null;

            try{
                $qrResponse = Http::post(route('vqr.customer.generate'),[
                    'orderId' => (string) $donHang->madh,
                    'amount' => (string) (int) round($donHang->tongtien),
                    'content' => (string) $donHang->content,
                ]);

                if($qrResponse->successful()){
                    $qrJson = $qrResponse->json();
                    $qrInfo = data_get($qrJson,'object');
                    $qrLink = data_get($qrJson,'object.qrLink');
                }

            }
            catch(\Exception $e){
                Log::warning('Không thể lấy được liên kết đến thanh toán qr của mã đơn hàng ('. $donHang->madh .')',[
                    'message' => $e->getMessage(),
                ]);
            }

            //Giả lập xử lý Timer đếm ngược (Do api môi trường test KHÔNG HỖ TRỢ)
            if ($qrInfo) {
                $now = now();
                $expiresAt = $now->copy()->addMinutes(15);

                $donHang->update([
                    'qr_created_at' => $now,
                    'qr_expires_at' => $expiresAt,
                ]);

                // Ghi đè thay đổi lên record có sẵn => để giao diện popup dùng giá trị mới nhất (metaData)
                $donHang->refresh();
            }



            session(['madh_cho_thanh_toan' => $donHang->madh]);
            session()->forget('don_hang_tam');

            $donhang       = $donhangtmp;
            $open_qr_modal = true;

            return view('KhachHangView.donhangTmp_Preview', compact('donhang', 'donHang', 'donhangtmp_KH', 'donhangtmp_SP', 'qrInfo', 'qrLink', 'open_qr_modal'))->with('open_qr_modal', true);
        
            } catch (\Exception $e) {
            Log::error('Lỗi gọi/kích hoạt môđun buyNow từ xacNhanDatHang đến KhachHangController', [
                'message' => $e->getMessage(),
                'makh'    => $maKhachHang,
                'masp'    => $masp,
            ]);

            return redirect()->back()->with('error', $e->getMessage() ?: 'Không thể xác nhận đặt hàng.');
        }
    }

    public function HuyDatHang()
    {
        //

        session()->forget('don_hang_tam');

        $ten_avatar = session('ten_khach_hang');

        return redirect()->route('sanphamIntroduce.index',compact('ten_avatar'))->with('message', 'Đơn hàng đã hủy. Bạn được chuyển về trang "Products"');
    }

    // Trong KhachHangController
    public function kiemTraTrangThaiDonHang($madh) {

        $maKhachHang = session('ma_khach_hang');
        if (!$maKhachHang) {
            return response()->json(['authenticated' => false], 401);
        }

        $donHang = DonHang::where('madh', (int) $madh)
            ->where('makh', (int) $maKhachHang)
            ->first();

        if (!$donHang) {
            return response()->json(['found' => false], 404);
        }

        $isExpired = false;
        if (
            (string) $donHang->trangthai_thanhtoan !== 'DA_THANH_TOAN'
            && !empty($donHang->qr_expires_at)
            && now()->greaterThan($donHang->qr_expires_at)
        ) {
            $isExpired = true;
            if ((string) $donHang->trangthai_donhang !== 'DA_HUY') {
                DonHang::where('madh', (int) $donHang->madh)->update([
                    'trangthai_donhang' => 'DA_HUY',
                ]);
                $donHang->refresh();
            }
        }

        return response()->json([
            'found'               => true,
            'madh'                => (int) $donHang->madh,
            'trangthai_thanhtoan' => $donHang->trangthai_thanhtoan,
            'trangthai_donhang'   => $donHang->trangthai_donhang,
            'paid_at'             => $donHang->paid_at ? (string) $donHang->paid_at : null,
            'qr_expires_at'       => $donHang->qr_expires_at ? (string) $donHang->qr_expires_at : null,
            'is_expired'          => $isExpired,
        ]);
    }

}
