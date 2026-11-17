<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ĐĂNG KÝ TÀI KHOẢN</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <h1>Đăng ký tài khoản</h1>

    @if (session('auth_message'))
        <div class="auth-message">
            {{ session('auth_message') }}
        </div>
    @endif
    <div class="register-container">
         
        <form action="{{ route('register.post') }}" method="post" class="register-form">
            @csrf
            <div class="form-field">
                <label>Tên: </label>
                <input type="text" name="hoten" value="{{ old('hoten') }}">
                @error('hoten')
                    <span class="err-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-field">
                <label>Email/Số điện thoại: </label>
                <input type="text"  name="identifier" value="{{ old('identifier') }}">
                @error('identifier')
                    <span class="err-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-field">
                <label>Mật khẩu: </label>
                <input type="password" name="matkhau">
                @error('matkhau')
                    <span class="err-message">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="auth-btn">Đăng ký</button>
        </form>
        
        
    </div>

</body>
</html>