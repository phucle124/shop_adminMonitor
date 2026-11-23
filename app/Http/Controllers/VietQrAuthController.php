<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use \Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


/*Chiều đi tới VietQR*/
class VietQrAuthController extends Controller
{
    //
    //Lấy token từ api của VietQR với method POST
    public function getVietQrAccessToken(): string 
    {
        return Cache::remember('vietqr_access_token', now()->addMinutes(50), function () {
            $username = 'customer-deviceshop-user26728';
            $password = 'Y3VzdG9tZXItZGV2aWNlc2hvcC11c2VyMjY3Mjg=';

            if (empty($username) || empty($password)) {
                throw new Exception('Thiếu cấu hình VIETQR_AUTH_USERNAME hoặc VIETQR_AUTH_PASSWORD');
            }

            $response = Http::withBasicAuth($username, $password)
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->post('https://dev.vietqr.org/vqr/api/token_generate',[]);

            if ($response->failed()) {
                throw new Exception('Lấy token VietQR thất bại (HTTP ' . $response->status() . ')');
            }

            $data = $response->json();
            $token = $data['access_token'] ?? data_get($data, 'data.access_token');

            if (empty($token)) {
                throw new Exception('Không tìm thấy access_token trong phản hồi của VietQR');
            }

            return $token;
        });
    }


    //Tạo Mã QR bằng api của VietQR
    public function getCustomerQr(Request $request, VietQrAuthController $authController)
    {
        $qrEndpoint = 'https://api.vietqr.org/vqr/api/qr/generate-customer';

                        /***************************Ghi chú****************************/
        $info = [       //info : NganHanginfo + DonHanginfo + field bắt buộc và optional của vietqr
            'bankCode'     => $request->input('bankCode', 'BIDV'),
            'bankAccount'  => $request->input('bankAccount', '8910920473'),
            'userBankName' => $request->input('userBankName', 'LE HOANG PHUC'),
            'amount'       => (string) $request->input('amount', '10000'),
            'content'      => $request->input('content', 'Test Qr'),
            'qrType'       => (int) $request->input('qrType', 0),
            'orderId'      => $request->input('orderId', 'DHtest'),
            'transType'    => $request->input('transType', 'C'),
            'urlLink'      => $request->input('urlLink', 'https://shop-adminmonitor.onrender.com/products'),
        ];

        try {
            $token = $authController->getVietQrAccessToken();

            //Gọi tới API $qrEndpoint tạo QR với payload là $info bằng METHOD POST
            //Và hứng "data" hoặc {"status" , "message"} vào biến $response
            $response = Http::withToken($token)->acceptJson()->timeout(15)->post($qrEndpoint, $info);

            if ($response->failed()) {

                return response()->json([
                    'status'       => $response->status(),
                    'message' => 'Gọi API tạo mã QR từ VietQR thất bại', //message nội bộ
                    'object'       => $response->json() ?? $response->body(),
                ], $response->status());    //status code từ VietQR
            }

            return response()->json([
                // 'status'       => $response->status(),   //Do Response từ api này có dạng là "data", KO PHẢI là status + message
                'message' => 'Tạo mã QR thành công', //message nội bộ
                'object'       => $response->json() ?? $response->body(),
            ], 200);

        } catch (Exception $e) {

            Log::error($e->getMessage()); //Thay vì 'message' => $e->getMessage()

            return response()->json([
                'message' => 'Đã xảy ra lỗi hệ thống khi tạo mã QR',
                'object'       => null,
            ], 500);
        }
    }


    public function tokenGenerate(){
        try {
            $token = $this->getVietQrAccessToken();

            return response()->json([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => 300,

            ], 200);
        } catch (Exception $e) {
            Log::error('Lỗi lấy token từ VietQR: ' . $e->getMessage());

            return response()->json([
                'error'        => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
   
    
}
