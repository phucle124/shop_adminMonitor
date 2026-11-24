<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
   
</head>
<body>
    <header>
        <div class="header-bar-container">
            <a href="{{ route('KhachHang.index') }}">
                <span class="shop-name">DeviceShop</span>
            </a>
            <div class="profile-container">
                <img src="{{ asset('storage/avatar_icon/iconMale.svg') }}" alt="Avatar">
                <span class="avatar-name">{{ $donhang['khach_hang']['ten_khach_hang'] ?? session('ten_khach_hang') }}</span>
            </div>
        </div>
    </header>

    <div class="checkout-container">
        <h1>Xem Lại Đơn Hàng Tạm Thời</h1>

        @if (session('error'))
            <div class="alert alert-danger" style="padding: 10px 14px; margin-bottom: 14px; background: #fee2e2; color: #991b1b; border-radius: 6px;">
                {{ session('error') }}
            </div>
        @endif

 
        <div class="section-box">
            <h3>Thông tin người mua</h3>
            <div class="info-row">
                <span>Mã khách hàng:</span>
                <strong>{{ $donhang['khach_hang']['ma_khach_hang'] }}</strong>
            </div>
            <div class="info-row">
                <span>Họ và tên:</span>
                <strong>{{ $donhang['khach_hang']['ten_khach_hang'] }}</strong>
            </div>
        </div>

     
        <div class="section-box">
            <h3>Sản phẩm đã chọn</h3>
            <div class="product-summary">
                <img src="{{ asset('storage/' . $donhang['san_pham']['hinh']) }}" alt="{{ $donhang['san_pham']['tensp'] }}">
                <div class="product-details">
                    <h4 style="margin: 0 0 10px 0;">{{ $donhang['san_pham']['tensp'] }}</h4>
                    <p style="margin: 0; color: #666;">Đơn giá: {{ number_format($donhang['san_pham']['dongia'], 0, ',', '.') }} đ</p>
                </div>
                <div>
                    <span>Số lượng: <strong>{{ $donhang['san_pham']['soluong_mua'] }}</strong></span>
                </div>
            </div>

            <div class="total-price">
                Thành tiền: {{ number_format($donhang['san_pham']['tongtien'], 0, ',', '.') }} đ
            </div>
        </div>

      
        <div class="form-actions">
            <a href="{{ route('sanphamIntroduce.index') }}" class="btn btn-back"><- Chọn sản phẩm khác</a>

            <form action="{{ route('donhang.confirm') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-confirm">Xác Nhận Đặt Hàng</button>
            </form>

            <a href="{{ route('donhangtmp.cancel') }}" class="btn btn-cancel">Hủy</a>
        </div>
    </div>

    {{-- POPUP --}}
    @if (!empty($open_qr_modal) && isset($donHang))
        <div class="modal-overlay" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 16px;">
            <div class="modal-box" style="background: #ffffff; width: 100%; max-width: 440px; border-radius: 12px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">

                <h2 style="margin: 0 0 14px 0; font-size: 20px; color: #0f172a;">
                    Thanh Toán Đơn Hàng #{{ $donHang->madh }}
                </h2>

                <div style="line-height: 1.65; margin-bottom: 14px; font-size: 14px; color: #334155;">
                    <div><strong>Mã đơn hàng:</strong> {{ $donHang->madh }}</div>
                    <div>
                        <strong>Tổng tiền:</strong>
                        <span style="color: #2563eb; font-weight: bold;">
                            {{ number_format((float) ($qrInfo['amount'] ?? $donHang->tongtien), 0, ',', '.') }} đ
                        </span>
                    </div>
                    <div><strong>Trạng thái thanh toán:</strong> {{ $donHang->trangthai_thanhtoan }}</div>
                    <div><strong>Trạng thái đơn hàng:</strong> {{ $donHang->trangthai_donhang }}</div>

                    @if (!empty($donHang->qr_expires_at))
                        <div style="margin-top: 6px; padding: 8px 10px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; color: #92400e; font-size: 13px;">
                            <strong>Hiệu lực QR (15 phút):</strong> hết hạn lúc {{ $donHang->qr_expires_at }}
                        </div>
                    @endif

                    @if (!empty($qrInfo))
                        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 10px 0;">
                        <div><strong>Ngân hàng:</strong> {{ $qrInfo['bankName'] ?? '' }} ({{ $qrInfo['bankCode'] ?? '' }})</div>
                        <div><strong>Số tài khoản:</strong> {{ $qrInfo['bankAccount'] ?? '' }}</div>
                        <div><strong>Chủ tài khoản:</strong> {{ $qrInfo['userBankName'] ?? '' }}</div>
                        <div><strong>Nội dung CK:</strong> {{ $qrInfo['content'] ?? (string) $donHang->madh }}</div>
                    @endif
                </div>

                <div style="text-align: center; padding: 16px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; margin-bottom: 14px;">
                    @if (!empty($qrLink))
                        <p style="margin: 0 0 10px 0; font-size: 13px; color: #1e293b;">
                            Nhấn nút bên dưới để mở trang VietQR và quét mã thanh toán:
                        </p>
                        <a href="{{ $qrLink }}" target="_blank" rel="noopener noreferrer"
                        style="display: inline-block; padding: 10px 18px; background: #2563eb; color: #ffffff; font-weight: 600; font-size: 14px; border-radius: 8px; text-decoration: none;">
                            Mở Trang QR VietQR
                        </a>
                        <div style="margin-top: 10px; font-size: 11px; color: #64748b; word-break: break-all;">
                            {{ $qrLink }}
                        </div>
                    @else
                        <div style="color: #b91c1c; font-size: 13px;">
                            Chưa lấy được liên kết QR VietQR. Bạn có thể chuyển khoản thủ công theo thông tin phía trên.
                        </div>
                    @endif
                </div>

                {{-- Form giả lập callback cho đơn hiện tại (madh số nguyên) --}}
                <form action="{{ route('bank.test.transaction-callback') }}" method="POST" style="margin-bottom: 14px;">
                    @csrf
                    <input type="hidden" name="madh" value="{{ $donHang->madh }}">
                    <input type="hidden" name="amount" value="{{ (int) round((float) ($qrInfo['amount'] ?? $donHang->tongtien)) }}">
                    <input type="hidden" name="content" value="{{ $donHang->madh }}">
                    <input type="hidden" name="bankAccount" value="{{ $qrInfo['bankAccount'] ?? '' }}">
                    <input type="hidden" name="bankCode" value="{{ $qrInfo['bankCode'] ?? '' }}">

                    <button type="submit"
                            style="width: 100%; padding: 10px 14px; background: #059669; color: #ffffff; font-weight: 600; font-size: 14px; border: 0; border-radius: 8px; cursor: pointer;">
                        Giả Lập Thanh Toán Thành Công (#{{ $donHang->madh }})
                    </button>
                </form>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <a href="{{ route('sanphamIntroduce.index') }}"
                    style="display: inline-block; padding: 8px 16px; background: #e2e8f0; color: #0f172a; border-radius: 8px; text-decoration: none; font-size: 14px;">
                        Đóng
                    </a>
                </div>

            </div>
        </div>
    @endif
</body>
</html>