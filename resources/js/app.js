let paymentInterval = null;

// 1. Hàm mở Modal và truyền thông tin đơn hàng vào
function openQrModal(madh, tongtien) {
    // Đổ dữ liệu lên UI Modal
    document.getElementById('modal-madh').innerText = '#DH' + madh;
    document.getElementById('modal-tongtien').innerText = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(tongtien);
    document.getElementById('modal-noidung').innerText = 'DH' + madh;

    // Cấu hình thông tin VietQR (Thay mã ngân hàng và STK của bạn vào đây)
    let bankId = "MB";          // Ví dụ: MB, VCB, TCB...
    let accountNo = "037356";   // Số tài khoản nhận tiền
    let qrUrl = `https://img.vietqr.io/image/${bankId}-${accountNo}-compact2.png?amount=${tongtien}&addInfo=DH${madh}`;
    
    document.getElementById('qr-image-src').src = qrUrl;

    // Hiển thị Modal bằng cách đổi display thành flex
    document.getElementById('qrPaymentModal').style.display = 'flex';

    // 2. Kích hoạt vòng lặp kiểm tra trạng thái thanh toán ngầm (mỗi 3 giây)
    startCheckingPayment(madh);
}

// 3. Hàm đóng Modal và hủy tiến trình kiểm tra
function closeQrModal() {
    document.getElementById('qrPaymentModal').style.display = 'none';
    if (paymentInterval) {
        clearInterval(paymentInterval);
        paymentInterval = null;
    }
}

// 4. Hàm chạy ngầm gọi API kiểm tra trạng thái từ Backend
function startCheckingPayment(madh) {
    if (paymentInterval) clearInterval(paymentInterval);

    paymentInterval = setInterval(function() {
        fetch(`/don-hang/check-status/${madh}`)
            .then(response => response.json())
            .then(res => {
                if (res.is_paid) {
                    // Dừng vòng lặp
                    clearInterval(paymentInterval);

                    // Thông báo thành công và chuyển hướng đến trang hoàn tất
                    alert("🎉 Thanh toán thành công! Hệ thống đã xác nhận đơn hàng của bạn.");
                    window.location.href = `/don-hang/thanh-cong/${madh}`; // Đổi đường dẫn trang thành công của bạn ở đây
                }
            })
            .catch(err => {
                console.log("Đang chờ webhook phản hồi...");
            });
    }, 3000); // 3000ms = Gọi kiểm tra lại sau mỗi 3 giây
}