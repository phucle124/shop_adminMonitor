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
        <div class="modal-overlay" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); display: flex; align-items: center; justify-content: center; z-index: 9999;">
            <div class="modal-box" style="background: #ffffff; width: 100%; max-width: 420px; border-radius: 12px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                
                <h2 style="margin-top: 0; font-size: 20px; color: #0f172a;">Thanh Toán Đơn Hàng #{{ $donHang->madh }}</h2>
                
                <div style="line-height: 1.6; margin-bottom: 16px; font-size: 14px; color: #334155;">
                    <div><strong>Mã đơn hàng:</strong> {{ $donHang->madh }}</div>
                    <div><strong>Tổng tiền:</strong> <span style="color: #2563eb; font-weight: bold;">{{ number_format($donHang->tongtien, 0, ',', '.') }} đ</span></div>
                    <div><strong>Trạng thái:</strong> {{ $donHang->trangthai_thanhtoan }}</div>
                </div>

                <!-- Hiển thị QR Code từ PHP Controller -->
                <div style="text-align: center; padding: 16px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; margin-bottom: 20px;">
                    @if ($qrLink)
                        <img src="{{ $qrLink }}" alt="Mã VietQR Đơn hàng #{{ $donHang->madh }}" style="max-width: 220px; width: 100%; height: auto; border-radius: 6px;">
                        <p style="margin: 8px 0 0 0; font-size: 12px; color: #64748b;">Quét mã bằng ứng dụng ngân hàng để hoàn tất</p>
                    @else
                        <div style="color: #b91c1c; font-size: 13px;">
                            Đã khởi tạo đơn hàng thành công nhưng chưa lấy được liên kết QR VietQR.
                        </div>
                    @endif
                </div>

                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <a href="{{ route('sanphamIntroduce.index') }}" class="btn btn-cancel" style="text-decoration: none; text-align: center;">Đóng</a>
                </div>

            </div>
        </div>
    @endif
</body>
</html>