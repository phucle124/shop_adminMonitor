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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
    public function transactionSync(Request $request){

        $secretKey    = (string) (env('VIETQR_JWT_SECRET') ?: 'abcdefghijklmnopqrstuvwxyzzyxwvutsrqponmlkjihgfedcbaZYXWVUTSRQPONMLKJIHGFEDCBA');
        $algo         = (string) (env('VIETQR_JWT_TYPE') ?: 'HS512');
        $bypassVerify = filter_var(env('VIETQR_BYPASS_TOKEN_VERIFY', true), FILTER_VALIDATE_BOOLEAN);

        $authHeader = (string) (
            $request->header('Authorization')
            ?? $request->server('HTTP_AUTHORIZATION')
            ?? $request->server('REDIRECT_HTTP_AUTHORIZATION')
            ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '')
            ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '')
        );

        $token    = preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m) ? trim($m[1]) : '';
        $jwtParts = $token !== '' ? explode('.', $token) : [];

        Log::info('[VQR][transactionSync] Raw auth check', [
            'auth_header_length' => strlen($authHeader),
            'auth_header_sample' => substr($authHeader, 0, 45),
            'token_length'       => strlen($token),
            'jwt_parts_count'    => count($jwtParts),
            'bypass_verify'      => $bypassVerify,
        ]);

        if ($token === '' || !preg_match('/^Bearer\s+.+$/i', $authHeader)) {
            return $this->respondWithError(
                new ErrorResponse(true, 'INVALID_AUTH_HEADER', 'Authorization header is missing or invalid', null),
                401
            );
        }

        if (!$bypassVerify && !$this->validateToken($token, $secretKey, $algo)) {
            return $this->respondWithError(
                new ErrorResponse(true, 'INVALID_TOKEN', 'Invalid or expired token', null),
                401
            );
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            $data = $request->all();
        }

        Log::info('Payload được bắn về', [
            'payload' => $data,
        ]);

        $callback = new TransactionCallback($data);

        try {
            $refTransactionId = DB::transaction(function () use ($callback) {
                // Chuẩn hóa kiểu dữ liệu theo đặc tả VietQR & schema DB
                $rawOrderId = trim((string) ($callback->orderId ?? ''));
                if ($rawOrderId !== '' && is_numeric($rawOrderId)) {
                    $orderId = (int) $rawOrderId;
                } else {
                    $rawContent = trim((string) ($callback->content ?? ''));
                    $orderId = preg_match('/\b(\d+)\b/', $rawContent, $m) ? (int) $m[1] : 0;
                }

                $amount          = (int) ($callback->amount ?? 0);
                $transType       = strtoupper(trim((string) ($callback->transType ?? '')));
                $content         = trim((string) ($callback->content ?? ''));
                $bankAccount     = trim((string) ($callback->bankaccount ?? ''));
                $transactionId   = trim((string) ($callback->transactionid ?? ''));
                $referenceNumber = trim((string) ($callback->referencenumber ?? ''));
                $transactionTime = (int) ($callback->transactiontime ?? 0);
                $maNganHang      = trim((string) ($callback->serviceCode ?? ''));

                $maTraCuuQr = $transactionId !== ''
                    ? $transactionId
                    : ($referenceNumber !== '' ? $referenceNumber : null);

                if ($orderId <= 0 || $amount <= 0) {
                    throw new \Exception('Mã đơn hàng (madh) hoặc số tiền chuyển khoản QR không hợp lệ (orderId=' . $orderId . ', amount=' . $amount . ')');
                }

                $donHang = DonHang::with('chitiet_donhangs')
                    ->where('madh', $orderId)
                    ->lockForUpdate()
                    ->first();

                if (!$donHang) {
                    throw new \Exception('Đơn hàng #' . $orderId . ' không tồn tại!');
                }

                if ((string) $donHang->trangthai_thanhtoan === 'DA_THANH_TOAN') {
                    return $maTraCuuQr;
                }

                if (!empty($donHang->qr_expires_at) && now()->greaterThan($donHang->qr_expires_at)) {
                    DonHang::where('madh', (int) $donHang->madh)->update([
                        'trangthai_donhang' => 'DA_HUY',
                    ]);

                    throw new \Exception('Đơn hàng #' . $donHang->madh . ' đã quá thời hạn 15 phút, không thể ghi nhận thanh toán.');
                }

                if ((string) $donHang->trangthai_donhang === 'DA_HUY') {
                    throw new \Exception('Đơn hàng #' . $donHang->madh . ' đang ở trạng thái DA_HUY.');
                }

                $soTienFloat  = (float) $amount;
                $tongTienDon  = (float) $donHang->tongtien;
                $noiDungCk    = $content;
                $maNganHangCk = $maNganHang;

                if ($soTienFloat < $tongTienDon) {
                    GiaoDichQR::create([
                        'ma_tra_cuu_qr' => $maTraCuuQr,
                        'sotien'        => $soTienFloat,
                        'noi_dung_ck'   => $noiDungCk,
                        'stk_ngan_hang' => $bankAccount,
                        'ma_ngan_hang'  => $maNganHangCk,
                        'trang_thai_qr' => 'FAILED',
                        'madh'          => (int) $donHang->madh,
                    ]);

                    throw new \Exception('Số tiền chi trả nhỏ hơn số tiền đơn hàng yêu cầu');
                }

                $paidAt = now();
                $updatedRows = DonHang::where('madh', (int) $donHang->madh)->update([
                    'trangthai_donhang'   => 'DA_XAC_NHAN',
                    'trangthai_thanhtoan' => 'DA_THANH_TOAN',
                    'paid_at'             => $paidAt,
                ]);

                Log::info('[VQR][transactionSync] Đã cập nhật đơn hàng', [
                    'madh'         => (int) $donHang->madh,
                    'updated_rows' => $updatedRows,
                    'paid_at'      => (string) $paidAt,
                ]);

                // Trừ tồn kho sản phẩm theo từng dòng chitiet_donhang
                foreach ($donHang->chitiet_donhangs as $chiTiet) {
                    $maSp       = (int) $chiTiet->masp;
                    $soLuongMua = max(0, (int) $chiTiet->soluong_mua);

                    $sanPham = SanPham::lockForUpdate()->find($maSp);
                    if ($sanPham) {
                        $tonKhoHienTai = (int) $sanPham->soluong_tonkho;
                        $sanPham->soluong_tonkho = max(0, $tonKhoHienTai - $soLuongMua);
                        $sanPham->save();
                    }
                }

                // Ghi nhận giao dịch QR thành công
                GiaoDichQR::create([
                    'ma_tra_cuu_qr' => $maTraCuuQr,
                    'sotien'        => $soTienFloat,
                    'noi_dung_ck'   => $noiDungCk,
                    'stk_ngan_hang' => $bankAccount,
                    'ma_ngan_hang'  => $maNganHangCk,
                    'trang_thai_qr' => 'SUCCESS',
                    'madh'          => (int) $donHang->madh,
                ]);

                return $maTraCuuQr ?? 'GeneratedRefTransactionId';
            });

            $res = new SuccessResponse(
                false,
                null,
                'Transaction processed successfully',
                new TransactionResponseObject((string) $refTransactionId)
            );

            return response()->json($res, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi xử lý transactionSync VietQR', [
                'message' => $e->getMessage(),
                'orderId' => (string) ($callback->orderId ?? ''),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return $this->respondWithError(
                new ErrorResponse(true, 'TRANSACTION_FAILED', $e->getMessage(), null),
                400
            );
        }
    }

    private function validateToken(string $token, string $secretKey, string $algo): bool{
        try {
            JWT::decode($token, new Key($secretKey, $algo));
            return true;
        } catch (\Exception $e) {
            Log::warning('[VQR][validateToken] Failed', [
                'message' => $e->getMessage(),
                'algo'    => $algo,
            ]);
            return false;
        }
    }

    

    private function respondWithError(ErrorResponse $errorResponse, int $statusCode = 400)
    {
        return response()->json($errorResponse, $statusCode);
    }



    public function testTransactionCallback(Request $request){
        try {
            $accessToken = Cache::get('vietqr_access_token');

            $info = [
                'bankAccount' => (string) $request->input('bankAccount', ''),
                'content'     => (string) $request->input('content', ''),
                'amount'      => (string) $request->input('amount', ''),
                'bankCode'    => (string) $request->input('bankCode', ''),
                'transType'   => 'C',
            ];

            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->post('https://dev.vietqr.org/vqr/bank/api/test/transaction-callback', $info);

            Log::info('VietQR raw test-callback response', [
                'http_status' => $response->status(),
                'body'        => $response->body(),
                'sent_info'   => $info,
            ]);  

            $body   = $response->json() ?? [];
            $status = strtoupper((string) (
                $body['status']
                ?? data_get($body, 'data.status')
                ?? ($response->successful() ? 'SUCCESS' : 'FAILED')
            ));

            if ($status === 'SUCCESS') {
                return response()->json([
                    'status'  => 'SUCCESS',
                    'message' => '',
                ], 200);
            }

            $errCode = (string) (
                $body['message']
                ?? $body['errorReason']
                ?? data_get($body, 'data.message')
                ?? ('HTTP_' . $response->status())
            );

            return response()->json([
                'status'  => 'FAILED',
                'message' => $errCode,
            ], $response->successful() ? 200 : $response->status());
        } catch (\Exception $e) {
            Log::error('Lỗi gọi testTransactionCallback VietQR', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'status'  => 'FAILED',
                'message' => 'TEST_CALLBACK_FAILED',
            ], 500);
        }
    }

    
}

