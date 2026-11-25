<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Quản trị viên</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <h1>Đăng nhập Quản trị viên</h1>

    @if (session('auth_message'))
        <div class="auth-message">
            {{ session('auth_message') }}
        </div>
    @endif

    @if (session('error'))
        <div class="err-message">
            {{ session('error') }}
        </div>
    @endif

    <div class="register-container">
        <form action="{{ route('admin.login.post') }}" method="post" class="register-form">
            @csrf

            <div class="form-field">
                <label for="email">Email:</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                >
                @error('email')
                    <span class="err-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-field">
                <label for="matkhau">Mật khẩu:</label>
                <input
                    id="matkhau"
                    type="password"
                    name="matkhau"
                    required
                >
                @error('matkhau')
                    <span class="err-message">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="auth-btn">Đăng nhập</button>
        </form>
    </div>
</body>
</html>