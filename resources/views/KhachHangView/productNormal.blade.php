<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sản phẩm phổ thông</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="productnormal-body">
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


    <h2 class="page-productnormal-title" style="color: blue">Phân Khúc Phổ Thông</h2>

    <div class="product-grid">
       
        @foreach ($dssp1 as $sp)
            <div class="product-card">
                <a href="{{ route('sanpham.show', ['masp' => $sp->masp]) }}">
                    <img class="product-img" src="{{ asset('storage/' . $sp->hinh) }}" >
                    <h2 class="tensp">{{ $sp->tensp }}</h2>
                    <p class="don-gia">{{ $sp->dongia }}</p>
             
                </a>
            </div>
        @endforeach

    </div>
</body>
</html>