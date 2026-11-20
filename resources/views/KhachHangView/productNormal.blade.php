<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sản phẩm phổ thông</title>
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
                <span class="avatar-name">{{ $ten_avatar }}</span>
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
    <h1 class="page-title" style="color: #3C86FF;">Phân Khúc Phổ Thông</h1>

    <div class="product-grid">
       
        @foreach ($dssp1 as $sp)
            <div class="product-card">
                <a href="{{ route('sanpham.show', ['masp' => $sp->masp]) }}">
                    <img class="product-img" src="{{ asset('storage/ . $sp->hinh') }}" >
                    <h2 class="tensp">{{ $sp->tensp }}</h2>
                    <p class="don-gia">{{ $sp->dongia }}</p>
                    <hr>
                </a>
            </div>
        @endforeach

    </div>
</body>
</html>