<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán thành công</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .success-card { background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; max-width: 450px; width: 100%; }
        .success-icon { font-size: 50px; color: #28a745; margin-bottom: 15px; }
        h2 { color: #333; margin-bottom: 10px; }
        p { color: #666; font-size: 15px; margin-bottom: 20px; }
        .btn-home { background-color: #007bff; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block; }
        .btn-home:hover { background-color: #0056b3; }
    </style>
</head>
<body>

    <div class="success-card">
        <div class="success-icon"></div>
        <h2>Thanh toán thành công!</h2>
        <p>Cảm ơn bạn đã thanh toán cho đơn hàng <strong>#DH{{ $madh ?? '' }}</strong>. Hệ thống đã xác nhận đơn hàng và đang chuẩn bị giao hàng cho bạn.</p>
        <a href="{{ url('/') }}" class="btn-home">Về trang chủ</a>
    </div>

</body>
</html>