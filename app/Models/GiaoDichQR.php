<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiaoDichQR extends Model
{
    // Tên bảng tương ứng trong DB (nếu khác chuẩn)
    protected $table = 'GiaoDichQRs';

    // Khóa chính của bảng (Mặc định là id)
    protected $primaryKey = 'id';

    // Các cột cho phép ghi dữ liệu hàng loạt (Mass Assignment)
    protected $fillable = [
        // 'ten_cot',
    ];

    // Các cột muốn ẩn khi chuyển sang JSON/Array (Mật khẩu, token...)
    protected $hidden = [
        // 'password',
    ];

    // Ép kiểu dữ liệu cho các cột (ví dụ: ngày tháng, boolean, json)
    protected $casts = [
        // 'email_verified_at' => 'datetime',
    ];

    // Bật/Tắt tự động quản lý 2 cột created_at và updated_at
    public $timestamps = true;
}
