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
        <div id="qrPaymentPanel"
            data-status-url="{{ route('khachhang.donhang.trang-thai', ['madh' => $donHang->madh]) }}"
            data-expires-at="{{ $donHang->qr_expires_at ?? '' }}"
            style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;">

            <div style="font-size: 13px; color: #334155; margin-bottom: 10px;">
                <div><strong>Thời gian còn lại:</strong> <span id="qrCountdown">--:--</span></div>
                <div id="liveOrderStatusText" style="margin-top: 4px;">
                    <strong>Trạng thái thanh toán:</strong> {{ $donHang->trangthai_thanhtoan }}
                </div>
            </div>

            <button type="button"
                    id="btnCheckPaymentStatus"
                    style="width: 100%; padding: 10px 14px; background: #0f172a; color: #ffffff; font-weight: 600; font-size: 14px; border: 0; border-radius: 8px; cursor: pointer;">
                Tôi đã chuyển khoản — Kiểm tra trạng thái
            </button>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const panel = document.getElementById('qrPaymentPanel');
                if (!panel) return;

                const statusUrl = panel.dataset.statusUrl;
                const expiresAtRaw = panel.dataset.expiresAt;
                const countdownEl = document.getElementById('qrCountdown');
                const statusTextEl = document.getElementById('liveOrderStatusText');
                const checkBtn = document.getElementById('btnCheckPaymentStatus');

                let pollTimer = null;
                let countdownTimer = null;

                async function checkStatus(manual = false) {
                    try {
                        if (manual && checkBtn) {
                            checkBtn.disabled = true;
                            checkBtn.textContent = 'Đang kiểm tra thanh toán...';
                        }

                        const res = await fetch(statusUrl, {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) return;

                        const data = await res.json();
                        if (!data.found) return;

                        if (data.trangthai_thanhtoan === 'DA_THANH_TOAN') {
                            clearInterval(pollTimer);
                            clearInterval(countdownTimer);
                            statusTextEl.innerHTML = `<strong style="color:#059669;">Đã thanh toán thành công!</strong> (Lúc: ${data.paid_at || 'vừa xong'})`;
                            if (checkBtn) checkBtn.style.display = 'none';
                            return;
                        }

                        if (data.is_expired || data.trangthai_donhang === 'DA_HUY') {
                            clearInterval(pollTimer);
                            clearInterval(countdownTimer);
                            if (countdownEl) countdownEl.textContent = '00:00 (Hết hạn)';
                            statusTextEl.innerHTML = `<strong style="color:#dc2626;">Mã QR đã quá 15 phút, đơn hàng đã chuyển sang DA_HUY.</strong>`;
                            if (checkBtn) checkBtn.disabled = true;
                            return;
                        }
                    } finally {
                        if (manual && checkBtn && checkBtn.style.display !== 'none') {
                            checkBtn.disabled = false;
                            checkBtn.textContent = 'Tôi đã chuyển khoản — Kiểm tra trạng thái';
                        }
                    }
                }

                if (expiresAtRaw && countdownEl) {
                    const targetTime = new Date(expiresAtRaw.replace(' ', 'T')).getTime();
                    const updateCountdown = () => {
                        const diffMs = targetTime - Date.now();
                        if (diffMs <= 0) {
                            countdownEl.textContent = '00:00';
                            checkStatus(false);
                            return;
                        }
                        const totalSec = Math.floor(diffMs / 1000);
                        const mins = String(Math.floor(totalSec / 60)).padStart(2, '0');
                        const secs = String(totalSec % 60).padStart(2, '0');
                        countdownEl.textContent = `${mins}:${secs}`;
                    };
                    updateCountdown();
                    countdownTimer = setInterval(updateCountdown, 1000);
                }

                if (checkBtn) {
                    checkBtn.addEventListener('click', () => checkStatus(true));
                }

                checkStatus(false);
                pollTimer = setInterval(() => checkStatus(false), 5000);
            });
        </script>
    @endif
</body>
</html>