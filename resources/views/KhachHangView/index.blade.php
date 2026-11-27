<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Shop introduce</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
</head>
<body>
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

    <div class="main-khachhangindex">
        <div class="main-khachhangindex-content">
            <div class="content-title-index">
                <p>Muốn thỏa sức với niềm đam mê công nghệ ?</p>
            </div>
            <div class="content-index">
                <p>Hãy đến với cửa hàng chúng tôi để trải nghiệm không gian lí tưởng, cung cấp cho quý khách những lựa chọn phù hợp với mọi loại nhu cầu cá nhân. 

                    Từ điện thoại, phụ kiện đến ốp lưng, sticker theo từng loại chủ đề. Ngoài ra còn nhận được tư vấn riêng về giải pháp và hiểu rõ mong muốn của quý khách.

                    Chúc quý khách có một ngày vui vẻ và may mắn.
                </p>
                <p class="slogan-index">Được phục vụ quý khách là niềm vinh hạnh của chúng tôi</p>
            </div>
        </div>
        <div class="main-khachhangindex-img">
            <img src="{{ asset('storage/images/index-img.svg') }}">
        </div>
    </div>
</body>
</html>