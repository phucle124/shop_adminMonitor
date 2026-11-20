<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Chi tiết đơn hàng</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @vite(['resources/js/app.js'])
</head>
<body>
    <header>
        <div class="header-bar-container">
            <a href="{{ route('KhachHang.index') }}">
                <span class="shop-name">DeviceShop</span>
            </a>
            <div class="profile-container">
                <img src="{{ asset('storage/avatar_icon/iconMale.svg') }}">
                <span class="avatar-name">{{ $donhang['khach_hang']['ten_khach_hang'] }}</span>
            </div>
        </div>
    </header>
    {{-- <div class="nav-container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="{{ route('KhachHang.index') }}">Shop</a></li>
                <li><a href="{{ route('sanphamIntroduce.index') }}">Products</a></li>
                <li><a href="{{ route('KhachHang.index') }}">About us</a></li>
                <li><a href="{{ route('KhachHang.index') }}">Contacts</a></li>
            </ul>
        </nav>
    </div> --}}

    <div class="checkout-container">
        <h1>Xem Lại Đơn Hàng Tạm Thời</h1>

        <!-- 1. Thông tin khách hàng -->
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

        <!-- 2. Thông tin sản phẩm tạm thời -->
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

        <!-- 3. Khu vực nút bấm hành động -->
        <div class="form-actions">
            <!-- Nút quay lại mua tiếp / hủy đơn tạm -->
            <a href="{{ route('KhachHang.index') }}" class="btn btn-back"><- Chọn sản phẩm khác</a>

            <!-- Form gửi yêu cầu Xác nhận đặt hàng chính thức vào Database -->
            <form action="#" method="POST">
                @csrf
                <a type="submit" class="btn btn-cancel">Hủy</a>
                <button type="submit" class="btn btn-confirm">Xác Nhận Đặt Hàng</button>
            </form>
        </div>
    </div>


    <!-- Popup QR Thanh Toán -->
    <div id="qrPaymentModal" class="qr-modal-overlay">
        <div class="qr-modal-container">
            
            <!-- Header Modal -->
            <div class="qr-modal-header">
                <span>Quét mã QR thanh toán</span>
                <button type="button" class="qr-modal-close" onclick="closeQrModal()">&times;</button>
            </div>

            <!-- Body Modal -->
            <div class="qr-modal-body">
                <div class="qr-info-box">
                    <p>Mã đơn hàng: <strong id="modal-madh" style="color: #333;">--</strong></p>
                    <h3 id="modal-tongtien">0 đ</h3>
                </div>

                <!-- Khung chứa ảnh QR động -->
                <div class="qr-image-wrapper">
                    <img id="qr-image-src" src="" alt="Mã QR Thanh Toán">
                </div>

                <!-- Lưu ý nội dung chuyển khoản -->
                <div class="qr-alert-box">
                    <strong>Lưu ý:</strong>
                    <ul>
                        <li>Nội dung CK: <strong id="modal-noidung" style="color: #007bff;">--</strong></li>
                        <li style="color: #28a745; margin-top: 4px; font-weight: bold;">Hệ thống đang chờ thanh toán tự động...</li>
                    </ul>
                </div>

                <!-- Trạng thái chờ (Loading) -->
                <div class="qr-loading-text">
                    <div class="qr-spinner"></div>
                    <span>Đang lắng nghe giao dịch từ ngân hàng...</span>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="qr-modal-footer">
                <button type="button" class="qr-btn-close" onclick="closeQrModal()">Hủy / Đóng</button>
            </div>

        </div>
    </div>
</body>
</html>
