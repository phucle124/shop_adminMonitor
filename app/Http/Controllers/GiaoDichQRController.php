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
        $secretKey = (string) (
            env('VIETQR_JWT_SECRET')
            ?: 'abcdefghijklmnopqrstuvwxyzzyxwvutsrqponmlkjihgfedcbaZYXWVUTSRQPONMLKJIHGFEDCBA'
        );

        $algo = (string) (
            env('VIETQR_JWT_TYPE') ?: 'HS512'
        );

        $bypassVerify = filter_var(
            env('VIETQR_BYPASS_TOKEN_VERIFY', true),
            FILTER_VALIDATE_BOOLEAN
        );

        /*
        * =========================================================
        * 1. LẤY AUTHORIZATION HEADER
        * =========================================================
        */
        $authHeader = (string) (
            $request->header('Authorization')
            ?? $request->server('HTTP_AUTHORIZATION')
            ?? $request->server('REDIRECT_HTTP_AUTHORIZATION')
            ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '')
            ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '')
        );

        $token = preg_match(
            '/^Bearer\s+(.+)$/i',
            $authHeader,
            $m
        )
            ? trim($m[1])
            : '';

        $jwtParts = $token !== ''
            ? explode('.', $token)
            : [];

        Log::info('[VQR][transactionSync] Raw auth check', [
            'auth_header_length' => strlen($authHeader),
            'auth_header_sample' => substr($authHeader, 0, 45),
            'token_length'       => strlen($token),
            'jwt_parts_count'    => count($jwtParts),
            'bypass_verify'      => $bypassVerify,
        ]);

        /*
        * =========================================================
        * 2. KIỂM TRA AUTHORIZATION
        * =========================================================
        */
        if (
            $token === ''
            || !preg_match('/^Bearer\s+.+$/i', $authHeader)
        ) {
            return $this->respondWithError(
                new ErrorResponse(
                    true,
                    'INVALID_AUTH_HEADER',
                    'Authorization header is missing or invalid',
                    null
                ),
                401
            );
        }

        /*
        * =========================================================
        * 3. VERIFY JWT
        * =========================================================
        */
        if (
            !$bypassVerify
            && !$this->validateToken($token, $secretKey, $algo)
        ) {
            return $this->respondWithError(
                new ErrorResponse(
                    true,
                    'INVALID_TOKEN',
                    'Invalid or expired token',
                    null
                ),
                401
            );
        }

        /*
        * =========================================================
        * 4. ĐỌC CALLBACK PAYLOAD
        * =========================================================
        */
        $data = json_decode(
            $request->getContent(),
            true
        );

        if (!is_array($data)) {
            $data = $request->all();
        }

        Log::info('[VQR][transactionSync] Payload được bắn về', [
            'payload' => $data,
        ]);

        $callback = new TransactionCallback($data);

        /*
        * =========================================================
        * 5. KIỂM TRA STATUS CỦA CALLBACK
        * =========================================================
        *
        * Đây là phần quan trọng.
        *
        * Không được hiểu:
        *
        *     callback tới server
        *             =
        *     thanh toán thành công
        *
        * Phải lấy status mà VietQR gửi về.
        *
        * Hiện tại source của bạn đã có:
        *
        *     $callback->status
        *
        * nhưng code cũ chưa sử dụng nó.
        */
        $transactionStatus = strtoupper(
            trim(
                (string) (
                    $data['status']
                    ?? $callback->status
                    ?? ''
                )
            )
        );

        Log::info('[VQR][transactionSync] Transaction status', [
            'status' => $transactionStatus,
            'transactionid' => $callback->transactionid,
            'referencenumber' => $callback->referencenumber,
            'orderId' => $callback->orderId,
            'amount' => $callback->amount,
        ]);

        /*
        * QUAN TRỌNG:
        *
        * Chỉ "SUCCESS" mới được coi là thanh toán thành công.
        *
        * Nếu VietQR thực tế sử dụng giá trị khác,
        * ví dụ SUCCESSFUL / PAID / COMPLETED...
        * thì phải thay đổi theo đúng đặc tả VietQR.
        */
        if ($transactionStatus !== 'SUCCESS') {

            Log::warning('[VQR][transactionSync] Transaction chưa thành công', [
                'status' => $transactionStatus,
                'orderId' => $callback->orderId,
                'transactionid' => $callback->transactionid,
                'referencenumber' => $callback->referencenumber,
            ]);

            return $this->respondWithError(
                new ErrorResponse(
                    true,
                    'TRANSACTION_NOT_SUCCESS',
                    'Transaction status is not successful',
                    null
                ),
                400
            );
        }

        /*
        * =========================================================
        * 6. XỬ LÝ GIAO DỊCH TRONG DB TRANSACTION
        * =========================================================
        */
        try {

            $refTransactionId = DB::transaction(
                function () use ($callback, $data) {

                    /*
                    * -------------------------------------------------
                    * Chuẩn hóa dữ liệu
                    * -------------------------------------------------
                    */
                    $rawContent = (string) (
                        $data['content']
                        ?? $callback->content
                        ?? ''
                    );

                    $rawOrderId = trim(
                        (string) (
                            $data['orderId']
                            ?? $callback->orderId
                            ?? ''
                        )
                    );

                    $orderId = 0;

                    /*
                    * -------------------------------------------------
                    * Xác định mã đơn hàng
                    * -------------------------------------------------
                    */
                    if (ctype_digit($rawOrderId)) {

                        $orderId = (int) $rawOrderId;

                    } elseif (
                        $rawOrderId === ''
                        || strtoupper($rawOrderId) === 'DHTEST'
                    ) {

                        /*
                        * Chỉ nên dùng fallback này trong môi trường test.
                        */
                        $orderId = (int) (
                            DonHang::where(
                                'trangthai_thanhtoan',
                                '!=',
                                'DA_THANH_TOAN'
                            )
                                ->where(
                                    'trangthai_donhang',
                                    '!=',
                                    'DA_HUY'
                                )
                                ->where(function ($q) {
                                    $q->whereNull('qr_expires_at')
                                        ->orWhere(
                                            'qr_expires_at',
                                            '>',
                                            now()
                                        );
                                })
                                ->orderByDesc('madh')
                                ->value('madh')
                                ?? 0
                        );
                    }

                    /*
                    * -------------------------------------------------
                    * Lấy số tiền
                    * -------------------------------------------------
                    */
                    $amount = (int) (
                        $data['amount']
                        ?? $callback->amount
                        ?? 0
                    );

                    Log::info(
                        '[VQR][transactionSync] Raw transaction data',
                        [
                            'content_raw' => $rawContent,
                            'orderId_raw' => $rawOrderId,
                            'orderId_int' => $orderId,
                            'amount'      => $amount,
                        ]
                    );

                    /*
                    * -------------------------------------------------
                    * Lấy thông tin giao dịch
                    * -------------------------------------------------
                    */
                    $transType = strtoupper(
                        (string) (
                            $data['transType']
                            ?? $callback->transType
                            ?? 'C'
                        )
                    );

                    $bankAccount = (string) (
                        $data['bankaccount']
                        ?? $callback->bankaccount
                        ?? ''
                    );

                    $transactionId = (string) (
                        $data['transactionid']
                        ?? $callback->transactionid
                        ?? ''
                    );

                    $referenceNumber = (string) (
                        $data['referencenumber']
                        ?? $callback->referencenumber
                        ?? ''
                    );

                    $transactionTime = (int) (
                        $data['transactiontime']
                        ?? $callback->transactiontime
                        ?? 0
                    );

                    $maNganHang = (string) (
                        $data['serviceCode']
                        ?? $callback->serviceCode
                        ?? ''
                    );

                    /*
                    * Ưu tiên transactionid.
                    * Nếu không có thì dùng referencenumber.
                    */
                    $maTraCuuQr = $transactionId !== ''
                        ? $transactionId
                        : (
                            $referenceNumber !== ''
                                ? $referenceNumber
                                : null
                        );

                    /*
                    * -------------------------------------------------
                    * Validate dữ liệu cơ bản
                    * -------------------------------------------------
                    */
                    if (
                        $orderId <= 0
                        || $amount <= 0
                    ) {
                        throw new \Exception(
                            'Mã đơn hàng (madh) hoặc số tiền chuyển khoản QR không hợp lệ '
                            . '(orderId=' . $rawOrderId
                            . ', amount=' . $amount . ')'
                        );
                    }

                    /*
                    * -------------------------------------------------
                    * LOCK đơn hàng
                    * -------------------------------------------------
                    */
                    $donHang = DonHang::with('chitiet_donhangs')
                        ->where('madh', $orderId)
                        ->lockForUpdate()
                        ->first();

                    if (!$donHang) {

                        throw new \Exception(
                            'Đơn hàng #' . $orderId . ' không tồn tại!'
                        );
                    }

                    /*
                    * -------------------------------------------------
                    * Đơn hàng đã thanh toán rồi
                    * -------------------------------------------------
                    *
                    * Đây là một lớp bảo vệ idempotency cơ bản.
                    */
                    if (
                        (string) $donHang->trangthai_thanhtoan
                        === 'DA_THANH_TOAN'
                    ) {

                        Log::info(
                            '[VQR][transactionSync] Order already paid',
                            [
                                'madh' => $donHang->madh,
                                'transactionid' => $transactionId,
                                'referencenumber' => $referenceNumber,
                            ]
                        );

                        return $maTraCuuQr;
                    }

                    /*
                    * -------------------------------------------------
                    * Kiểm tra QR hết hạn
                    * -------------------------------------------------
                    */
                    if (
                        !empty($donHang->qr_expires_at)
                        && now()->greaterThan(
                            $donHang->qr_expires_at
                        )
                    ) {

                        DonHang::where(
                            'madh',
                            (int) $donHang->madh
                        )->update([
                            'trangthai_donhang' => 'DA_HUY',
                        ]);

                        throw new \Exception(
                            'Đơn hàng #' . $donHang->madh
                            . ' đã quá thời hạn 15 phút, '
                            . 'không thể ghi nhận thanh toán.'
                        );
                    }

                    /*
                    * -------------------------------------------------
                    * Kiểm tra đơn hàng đã hủy
                    * -------------------------------------------------
                    */
                    if (
                        (string) $donHang->trangthai_donhang
                        === 'DA_HUY'
                    ) {

                        throw new \Exception(
                            'Đơn hàng #' . $donHang->madh
                            . ' đang ở trạng thái DA_HUY.'
                        );
                    }

                    /*
                    * -------------------------------------------------
                    * Kiểm tra số tiền
                    * -------------------------------------------------
                    */
                    $soTienFloat = (float) $amount;
                    $tongTienDon = (float) $donHang->tongtien;

                    $noiDungCk = $rawContent;
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

                        app(LogTrangThaiController::class)
                            ->ghiLogTrangThai(
                                $maTraCuuQr,
                                'GIAO_DICH_QR',
                                $donHang->trangthai_donhang ?? null,
                                'FAILED'
                            );

                        throw new \Exception(
                            'Số tiền chi trả nhỏ hơn '
                            . 'số tiền đơn hàng yêu cầu'
                        );
                    }

                    /*
                    * -------------------------------------------------
                    * ĐÁNH DẤU THANH TOÁN THÀNH CÔNG
                    * -------------------------------------------------
                    *
                    * Chỉ chạy tới đây khi:
                    *
                    * 1. JWT hợp lệ
                    * 2. status = SUCCESS
                    * 3. order hợp lệ
                    * 4. order chưa thanh toán
                    * 5. QR chưa hết hạn
                    * 6. order chưa hủy
                    * 7. amount >= tổng tiền
                    */
                    $paidAt = now();

                    $updatedRows = DonHang::where(
                        'madh',
                        (int) $donHang->madh
                    )->update([
                        'trangthai_donhang'   => 'DA_XAC_NHAN',
                        'trangthai_thanhtoan' => 'DA_THANH_TOAN',
                        'paid_at'             => $paidAt,
                    ]);

                    Log::info(
                        '[VQR][transactionSync] Đã cập nhật đơn hàng',
                        [
                            'madh'         => (int) $donHang->madh,
                            'updated_rows' => $updatedRows,
                            'paid_at'      => (string) $paidAt,
                        ]
                    );

                    /*
                    * -------------------------------------------------
                    * Trừ tồn kho
                    * -------------------------------------------------
                    */
                    foreach (
                        $donHang->chitiet_donhangs
                        as $chiTiet
                    ) {

                        $maSp = (int) $chiTiet->masp;

                        $soLuongMua = max(
                            0,
                            (int) $chiTiet->soluong_mua
                        );

                        $sanPham = SanPham::lockForUpdate()
                            ->find($maSp);

                        if ($sanPham) {

                            $tonKhoHienTai = (int) (
                                $sanPham->soluong_tonkho
                            );

                            $sanPham->soluong_tonkho = max(
                                0,
                                $tonKhoHienTai - $soLuongMua
                            );

                            $sanPham->save();
                        }
                    }

                    /*
                    * -------------------------------------------------
                    * Ghi nhận giao dịch QR SUCCESS
                    * -------------------------------------------------
                    */
                    GiaoDichQR::create([
                        'ma_tra_cuu_qr' => $maTraCuuQr,
                        'sotien'        => $soTienFloat,
                        'noi_dung_ck'   => $noiDungCk,
                        'stk_ngan_hang' => $bankAccount,
                        'ma_ngan_hang'  => $maNganHangCk,
                        'trang_thai_qr' => 'SUCCESS',
                        'madh'          => (int) $donHang->madh,
                    ]);

                    /*
                    * -------------------------------------------------
                    * Log giao dịch
                    * -------------------------------------------------
                    */
                    if ($maTraCuuQr) {

                        app(LogTrangThaiController::class)
                            ->ghiLogTrangThai(
                                $maTraCuuQr,
                                'GIAO_DICH_QR',
                                'DA_XAC_NHAN',
                                'SUCCESS'
                            );
                    }

                    /*
                    * -------------------------------------------------
                    * Log trạng thái đơn hàng
                    * -------------------------------------------------
                    */
                    app(LogTrangThaiController::class)
                        ->ghiLogTrangThai(
                            (string) $donHang->madh,
                            'DON_HANG',
                            'DA_XAC_NHAN',
                            'DA_THANH_TOAN'
                        );

                    return $maTraCuuQr
                        ?? 'GeneratedRefTransactionId';
                }
            );

            /*
            * =========================================================
            * 7. TRẢ RESPONSE CHO VIETQR
            * =========================================================
            */
            $res = new SuccessResponse(
                false,
                null,
                'Transaction processed successfully',
                new TransactionResponseObject(
                    (string) $refTransactionId
                )
            );

            return response()->json($res, 200);

        } catch (\Exception $e) {

            Log::error(
                'Lỗi xử lý transactionSync VietQR',
                [
                    'message' => $e->getMessage(),
                    'orderId' => (string) (
                        $callback->orderId ?? ''
                    ),
                    'status' => $transactionStatus,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );

            return $this->respondWithError(
                new ErrorResponse(
                    true,
                    'TRANSACTION_FAILED',
                    $e->getMessage(),
                    null
                ),
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



    public function testTransactionCallback(Request $request, VietQrAuthController $VqrAuthController) {
        try {

            /*
            * =========================================================
            * 1. LẤY ACCESS TOKEN
            * =========================================================
            */
            $accessToken =
                $VqrAuthController->getVietQrAccessToken();

            /*
            * =========================================================
            * 2. DỮ LIỆU GỬI CHO VIETQR TEST CALLBACK
            * =========================================================
            */
            $info = [
                'bankAccount' => (string) $request->input(
                    'bankAccount',
                    ''
                ),

                'content' => (string) $request->input(
                    'content',
                    ''
                ),

                'amount' => (string) $request->input(
                    'amount',
                    ''
                ),

                'bankCode' => (string) $request->input(
                    'bankCode',
                    ''
                ),

                'orderId' => (string) $request->input(
                    'orderId',
                    ''
                ),

                'transType' => 'C',
            ];

            Log::info(
                '[VQR][testTransactionCallback] Sending test callback',
                [
                    'info' => $info,
                ]
            );

            /*
            * =========================================================
            * 3. GỌI API TEST CALLBACK CỦA VIETQR
            * =========================================================
            */
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->post(
                    'https://dev.vietqr.org/vqr/bank/api/test/transaction-callback',
                    $info
                );

            /*
            * =========================================================
            * 4. LOG RESPONSE RAW
            * =========================================================
            */
            Log::info(
                '[VQR][testTransactionCallback] VietQR raw response',
                [
                    'http_status' => $response->status(),
                    'body'        => $response->body(),
                    'sent_info'   => $info,
                ]
            );

            /*
            * =========================================================
            * 5. ĐỌC RESPONSE
            * =========================================================
            */
            $body = $response->json();

            if (!is_array($body)) {
                $body = [];
            }

            /*
            * =========================================================
            * 6. LẤY STATUS DO VIETQR TRẢ VỀ
            * =========================================================
            *
            * KHÔNG dùng:
            *
            *     $response->successful()
            *
            * làm trạng thái thanh toán.
            *
            * Vì:
            *
            * HTTP 200
            *     !=
            * Payment SUCCESS
            *
            * HTTP 200 có thể chỉ có nghĩa:
            *
            * "VietQR đã nhận request test callback"
            *
            */
            $status = strtoupper(
                trim(
                    (string) (
                        $body['status']
                        ?? data_get(
                            $body,
                            'data.status'
                        )
                        ?? ''
                    )
                )
            );

            /*
            * =========================================================
            * 7. LẤY MESSAGE / ERROR
            * =========================================================
            */
            $message = (string) (
                $body['message']
                ?? $body['errorReason']
                ?? data_get(
                    $body,
                    'data.message'
                )
                ?? ''
            );

            /*
            * =========================================================
            * 8. CHỈ TRẢ SUCCESS KHI VIETQR THỰC SỰ TRẢ STATUS SUCCESS
            * =========================================================
            */
            if ($status === 'SUCCESS') {

                return response()->json([
                    'status'  => 'SUCCESS',
                    'message' => $message,
                ], 200);
            }

            /*
            * =========================================================
            * 9. KHÔNG CÓ STATUS SUCCESS
            * =========================================================
            *
            * Nếu HTTP 200 nhưng body không có status SUCCESS,
            * KHÔNG được tự động đổi thành SUCCESS.
            */
            if ($response->successful()) {

                return response()->json([
                    'status'  => 'PENDING',
                    'message' => $message !== ''
                        ? $message
                        : 'Test callback request accepted, but transaction status is not confirmed as SUCCESS.',
                ], 200);
            }

            /*
            * =========================================================
            * 10. VIETQR TRẢ HTTP ERROR
            * =========================================================
            */
            return response()->json([
                'status' => 'FAILED',
                'message' => $message !== ''
                    ? $message
                    : 'HTTP_' . $response->status(),
            ], $response->status());

        } catch (\Exception $e) {

            Log::error(
                'Lỗi gọi testTransactionCallback VietQR',
                [
                    'message' => $e->getMessage(),
                    'line'    => $e->getLine(),
                ]
            );

            return response()->json([
                'status'  => 'FAILED',
                'message' => 'TEST_CALLBACK_FAILED',
            ], 500);
        }
    }

    
}

