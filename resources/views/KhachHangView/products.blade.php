<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <header>
        <div class="header-bar-container">
            <a href="{{ route('KhachHang.index') }}">
                <span class="shop-name">DeviceShop</span>
            </a>
            <div class="profile-container">
                <img src="{{ asset('storage/avatar_icon/iconMale.svg') }}">
                <span class="avatar-name">{ $ten_avatar }</span>
            </div>
        </div>
    </header>
    <div class="nav-container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="{{ route('KhachHang.index') }}">Shop</a></li>
                <li><a href="{{ route('KhachHang.index') }}">Products</a></li>
                <li><a href="{{ route('KhachHang.index') }}">About us</a></li>
                <li><a href="{{ route('KhachHang.index') }}">Contacts</a></li>
            </ul>
        </nav>
    </div>
    <div class="introduce-scripts">
        <h3>Tùy chọn phân khúc sản phẩm mà bạn muốn</h3>
        <p>Các nhóm sản phẩm phổ biến nhất hiện nay: </p>
    </div>
    <div class="product-container">
        <div class="product-card">
            <a href="/">
                <img class="img-normal" src="{{ asset('storage/images/typeNormal.svg') }}" alt="">
                <p class="typeNormal-title">Phổ thông</p>
            </a>
        </div>
        <div class="product-card">
            <a href="/">
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
</body>
</html>