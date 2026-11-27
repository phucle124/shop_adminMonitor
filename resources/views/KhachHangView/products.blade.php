<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="page-wrapper">
    @php
        $displayName = session('ten_khach_hang');
        $isLogin = !empty($displayName);
    @endphp

    <header>
        <div class="header-bar-container">
            <a href="{{ route('KhachHang.index') }}">
                <span class="shop-name">DeviceShop</span>
            </a>

            @if ($isLogin)
                <div class="profile-dropdown">
                    <button type="button" class="profile-trigger">
                        <div class="profile-container">
                            <img src="{{ asset('storage/avatar_icon/iconMale.svg') }}" alt="{{ $displayName ?? 'Khách hàng' }}">
                            <span class="avatar-name">{{ $displayName ?? 'Khách hàng' }}</span>
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
            @else
                <div class="auth-header-actions">
                    <a href="{{ route('register') }}" class="header-auth-btn header-register-btn">Đăng ký</a>
                    <a href="{{ route('login') }}" class="header-auth-btn header-login-btn">Đăng nhập</a>
                </div>
            @endif
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
    <div class="main-product-card">
        <div class="introduce-scripts">
            <h3>Tùy chọn phân khúc sản phẩm mà bạn muốn</h3>
            <p>Các nhóm sản phẩm phổ biến nhất hiện nay: </p>
        </div>
        <div class="product-container">
            <div class="product-card">
                <a href="{{ route('sanpham1.index') }}">
                    <img class="img-normal" src="{{ asset('storage/images/typeNormal.svg') }}" alt="">
                    <p class="typeNormal-title">Phổ thông</p>
                </a>
            </div>
            <div class="product-card">
                <a href="{{ route('sanpham2.index') }}">
                    <img class="img-special" src="{{ asset('storage/images/typeSpecial.svg') }}" alt="">
                    <p class="typeSpecial-title">Chuyên dụng</p>
                    
                </a>
            </div>
            <div class="product-card">
                <a href="/">
                    <img class="img-ai" src="{{ asset('storage/images/typeAI.svg') }}" alt="">
                    <p class="typeAI-title">Tích hợp AI</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>