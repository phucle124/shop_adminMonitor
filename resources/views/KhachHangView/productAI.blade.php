<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sản phẩm có tích hợp AI</title>
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

    <h1 class="page-title" style="color: #45FFA5;">Phân Khúc Tích Hợp AI</h1>

    <div class="product-grid">
        <!-- Sản phẩm 1 -->
        <div class="product-card">
            <img class="product-img" src="" >
            <h2 class="tensp">AI Core Station X1</h2>
            <p class="don-gia">45.000.000 đ</p>
        </div>

        <!-- Sản phẩm 2 -->
        <div class="product-card">
            <img class="product-img" src="" >
            <h2 class="tensp">Smart AI Node Pro</h2>
            <p class="don-gia">62.000.000 đ</p>
        </div>

        <!-- Sản phẩm 3 -->
        <div class="product-card">
            <img class="product-img" src="" >
            <h2 class="tensp">Neural Engine Hub</h2>
            <p class="don-gia">89.000.000 đ</p>
        </div>
    </div>
</body>
</html>