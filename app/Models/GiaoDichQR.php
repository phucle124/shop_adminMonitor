<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiaoDichQR extends Model
{
    // Tên bảng tương ứng trong DB (nếu khác chuẩn)
    protected $table = 'giaodichqr';

    protected $primaryKey = 'ma_giao_dich';

    protected $fillable = [
        'ma_tra_cuu_qr',
        'sotien',
        'qr_url_pic',
        'noi_dung_ck',
        'stk_ngan_hang',
        'ma_ngan_hang',
        'trang_thai_qr',
        'madh',
    ];


    // Các cột muốn ẩn khi chuyển sang JSON/Array (Mật khẩu, token...)
    protected $hidden = [
        // 'password',
    ];

    // Ép kiểu dữ liệu cho các cột (ví dụ: ngày tháng, boolean, json)
    protected $casts = [
        // 'email_verified_at' => 'datetime',
        'sotien'        => 'float',
        'trang_thai_qr' => 'string',
        'madh'          => 'integer',
    ];

    // Bật/Tắt tự động quản lý 2 cột created_at và updated_at
    public $timestamps = true;
}
