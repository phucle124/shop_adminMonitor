<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuanTriVien extends Model
{
    // Tên bảng tương ứng trong DB (nếu khác chuẩn)
    protected $table = 'quantrivien';

    // Khóa chính của bảng (Mặc định là id)
    protected $primaryKey = 'maqr';

    // Các cột cho phép ghi dữ liệu hàng loạt (Mass Assignment)
    protected $fillable = [
        // 'ten_cot',
        'hoten',
        'email',
        'matkhau',
    ];

    // Các cột muốn ẩn khi chuyển sang JSON/Array (Mật khẩu, token...)
    protected $hidden = [
        // 'password',
        'matkhau',
    ];

    // Ép kiểu dữ liệu cho các cột (ví dụ: ngày tháng, boolean, json)
    protected $casts = [
        // 'email_verified_at' => 'datetime',
    ];

    // Bật/Tắt tự động quản lý 2 cột created_at và updated_at
    public $timestamps = true;
}
