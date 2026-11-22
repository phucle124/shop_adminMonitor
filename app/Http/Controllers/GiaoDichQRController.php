<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\GiaoDichQR;
use App\Models\SanPham;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



class TransactionCallback
{
    public $transactionid;
    public $transactiontime;
    public $referencenumber;
    public $amount;
    public $content;
    public $bankaccount;
    public $orderId;
    public $sign;
    public $terminalCode;
    public $urlLink;
    public $serviceCode;
    public $subTerminalCode;
    public $status;

    public function __construct($data)
    {
        $this->transactionid   = $data['transactionid'] ?? null;
        $this->transactiontime = $data['transactiontime'] ?? null;
        $this->referencenumber = $data['referencenumber'] ?? null;
        $this->amount          = $data['amount'] ?? null;
        $this->content         = $data['content'] ?? null;
        $this->bankaccount     = $data['bankaccount'] ?? null;
        $this->orderId         = $data['orderId'] ?? null;
        $this->sign            = $data['sign'] ?? null;
        $this->terminalCode    = $data['terminalCode'] ?? null;
        $this->urlLink         = $data['urlLink'] ?? null;
        $this->serviceCode     = $data['serviceCode'] ?? null;
        $this->subTerminalCode = $data['subTerminalCode'] ?? null;
        $this->status = $data['status'] ?? null;
    }
}

// Lớp model cho success response
class SuccessResponse {
    public $error;
    public $errorReason;
    public $toastMessage;
    public $object;

    public function __construct($error, $errorReason, $toastMessage, $object) {
        $this->error = $error;
        $this->errorReason = $errorReason;
        $this->toastMessage = $toastMessage;
        $this->object = $object;
    }
}

// Lớp model cho lỗi response
class ErrorResponse {
    public $error;
    public $errorReason;
    public $toastMessage;
    public $object;

    public function __construct($error, $errorReason, $toastMessage, $object) {
        $this->error = $error;
        $this->errorReason = $errorReason;
        $this->toastMessage = $toastMessage;
        $this->object = $object;
    }
}

class TransactionResponseObject{
    public $reftransactionid;

    public function __construct($reftransactionid){
        $this->reftransactionid = $reftransactionid;
    }
}


/*Chiều từ VietQR đổ về*/
class GiaoDichQRController extends Controller
{
    

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
            $tenNganHang= $data['bankCode'] ?? '';          // ma_ngan_hang    (giaodichqr)
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


    public function transactionSync(Request $request)
    {
        $secretKey = env('VIETQR_JWT_SECRET');
        $algo = env('VIETQR_JWT_TYPE');

        $authHeader = $request->header('Authorization') ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
        $bearerPrefix = 'Bearer ';

        if (empty($authHeader) || !str_starts_with($authHeader, $bearerPrefix)) {
            return $this->respondWithError(
                new ErrorResponse(true, 'INVALID_AUTH_HEADER', 'Authorization header is missing or invalid', null),
                401
            );
        }

        $token = substr($authHeader, strlen($bearerPrefix));

        if (!$this->validateToken($token, $secretKey, $algo)) {
            return $this->respondWithError(
                new ErrorResponse(true, 'INVALID_TOKEN', 'Invalid or expired token', null),
                401
            );
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            $data = $request->all();
        }

        $callback = new TransactionCallback($data);

        $orderId    = $callback->orderId;
        $soTien     = (float) ($callback->amount ?? 0);
        $noiDung    = $callback->content ?? '';
        $stk        = $callback->bankaccount ?? '';
        $tenNganHang= $callback->serviceCode ?? '';
        $maTraCuuQr = $callback->transactionid ?? $callback->referencenumber ?? null;

        if (empty($orderId) || $soTien <= 0) {
            return $this->respondWithError(
                new ErrorResponse(true, 'INVALID_PAYLOAD', 'Missing or invalid orderId/amount', null),
                400
            );
        }

        try {
            $refTransactionId = DB::transaction(function () use (
                $callback,
                $orderId,
                $soTien,
                $noiDung,
                $stk,
                $tenNganHang,
                $maTraCuuQr
            ) {
                $donHang = DonHang::with('chitiet_donhangs')
                    ->where('madh', $orderId)
                    ->lockForUpdate()
                    ->first();

                if (!$donHang) {
                    throw new Exception('Order not found');
                }

                // Tránh xử lý trùng lặp nếu đơn đã thanh toán
                if ($donHang->trangthai_thanhtoan === 'DA_THANH_TOAN') {
                    return $maTraCuuQr ?? 'ALREADY_PAID';
                }

                // Kiểm tra số tiền thanh toán có đủ không
                if ($soTien < (float) $donHang->tongtien) {
                    GiaoDichQR::create([
                        'ma_tra_cuu_qr' => $maTraCuuQr,
                        'sotien'        => $soTien,
                        'noi_dung_ck'   => $noiDung,
                        'stk_ngan_hang' => $stk,
                        'ten_ngan_hang' => $tenNganHang,
                        'trang_thai_qr' => $callback->status,
                        'madh'          => $donHang->madh,
                    ]);

                    throw new Exception('Payment amount is less than order total');
                }

                $donHang->update([
                    'trangthai_donhang'   => 'DA_XAC_NHAN',
                    'trangthai_thanhtoan' => 'DA_THANH_TOAN',
                ]);

                foreach ($donHang->chitiet_donhangs as $chiTiet) {
                    $sanPham = SanPham::lockForUpdate()->find($chiTiet->masp);
                    if ($sanPham) {
                        $sanPham->soluong_tonkho = max(0, $sanPham->soluong_tonkho - $chiTiet->soluong_mua);
                        $sanPham->save();
                    }
                }

                GiaoDichQR::create([
                    'ma_tra_cuu_qr' => $maTraCuuQr,
                    'sotien'        => $soTien,
                    'noi_dung_ck'   => $noiDung,
                    'stk_ngan_hang' => $stk,
                    'ten_ngan_hang' => $tenNganHang,
                    'trang_thai_qr' => $callback->status,
                    'madh'          => $donHang->madh,
                ]);

                return $callback->transactionid ?? $callback->referencenumber ?? 'GeneratedRefTransactionId';
            });

            $res = new SuccessResponse(false,null,'Transaction processed successfully',new TransactionResponseObject($refTransactionId));

            return response()->json($res, 200);
        } catch (Exception $e) {
            Log::error('Lỗi xử lý transactionSync VietQR', [
                'message' => $e->getMessage(),
                'orderId' => $orderId,
            ]);

            return $this->respondWithError(
                new ErrorResponse(true, 'TRANSACTION_FAILED', 'Transaction processing failed', null), 400);
        }
    }

    private function validateToken(string $token, string $secretKey, string $algorithm): bool
    {
        try {
            JWT::decode($token, new Key($secretKey, $algorithm));
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function respondWithError(ErrorResponse $errorResponse, int $statusCode = 400)
    {
        return response()->json($errorResponse, $statusCode);
    }
}
