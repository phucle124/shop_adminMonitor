<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Chi tiết sản phẩm</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    @php
        $displayName = $ten_avatar ?? session('ten_khach_hang');
    @endphp

    <header>
        <div class="header-bar-container">
            <a href="{{ route('KhachHang.index') }}">
                <span class="shop-name">DeviceShop</span>
            </a>

            <div class="profile-dropdown">
                <button type="button" class="profile-trigger">
                    <div class="profile-container">
                        <img src="{{ asset('storage/avatar_icon/iconMale.svg') }}" alt="{{ $displayName }}">
                        <span class="avatar-name">{{ $displayName }}</span>
                    </div>
                </button>

                <div class="profile-dropdown-menu">
                    <form action="{{ route('KhachHang.logout') }}" method="POST" class="profile-logout-form">
                        @csrf
                        <button type="submit" class="profile-logout-btn">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="nav-container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="{{ route('KhachHang.index') }}">Shop</a></li>
                <li><a href="{{ route('sanphamIntroduce.index') }}">Products</a></li>
                <li><a href="{{ route('KhachHang.index') }}">About us</a></li>
                <li><a href="{{ route('KhachHang.index') }}">Contacts</a></li>
            </ul>
        </nav>
    </div>

    <!-- Nội dung chi tiết sản phẩm -->
    <div class="product-detail-container">
        <!-- Hình ảnh lớn của sản phẩm -->
        <div class="product-image-box">
            <img src="{{ asset('storage/' . $sp->hinh) }}" alt="{{ $sp->tensp }}">
        </div>

        <!-- Thông tin chi tiết -->
        <div class="product-info-box">
            <h1 class="product-title">{{ $sp->tensp }}</h1>
            <div class="product-meta">
                <span>Phân khúc: <strong>{{ $sp->loaisp }}</strong></span> | 
                <span>Tồn kho: <strong>{{ $sp->soluong_tonkho }}</strong></span>
            </div>
            <div class="product-price">{{ number_format($sp->dongia, 0, ',', '.') }} đ</div>
            
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
            
            <h3>Mô tả sản phẩm</h3>
            <p class="product-desc">{{ $sp->mota }}</p>

            {{-- <form action="{{ route('OrderDetails.show') }}" method="post"> --}}
            <form action="{{ route('donhangtmp.post') }}" method="post">
                @csrf
                <input type="hidden" name="masp" value="{{ $sp->masp }}">
                <input type="hidden" name="tensp" value="{{ $sp->tensp }}">
                <input type="hidden" name="loaisp" value="{{ $sp->loaisp }}">
                <input type="hidden" name="hinh" value="{{ $sp->hinh }}">

                <input type="hidden" name="dongia" value="{{ $sp->dongia }}">

                {{-- Chỉ hiện thị Phần chọn số lượng mua --}}
                <label>Số lượng mua: </label><input type="number" name="soluong_mua" min="1" max="{{ $sp->soluong_tonkho }}">

                {{-- Chỉ hiện thị nút [Mua Ngay] --}}
                <button type="submit">Mua ngay</button>
            </form>

            
        </div>
        <a href="{{ route('sanpham1.index') }}" class="btn-back">Quay lại danh sách sản phẩm</a>
    </div>
</body>
</html>