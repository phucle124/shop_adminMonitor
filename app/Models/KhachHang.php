<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KhachHang extends Model
{
    // Tên bảng tương ứng trong DB (nếu khác chuẩn)
    protected $table = 'khachhang';

    // Khóa chính của bảng (Mặc định là id)
    protected $primaryKey = 'makh';

    // Các cột cho phép ghi dữ liệu hàng loạt (Mass Assignment)
    protected $fillable = [
        // 'ten_cot',
        'hoten',
        'email',
        'sdt',
        'matkhau',
        'gioitinh',
        'province_code',
        'province_name',
    ];

    // Các cột muốn ẩn khi chuyển sang JSON/Array (Mật khẩu, token...)
    // protected $hidden = [
    //     // 'password',
    // ];

    // // Ép kiểu dữ liệu cho các cột (ví dụ: ngày tháng, boolean, json)
    // protected $casts = [
    //     // 'email_verified_at' => 'datetime',
    // ];

    // Bật/Tắt tự động quản lý 2 cột created_at và updated_at
    public $timestamps = true;

    public function donhangs(){  //Có tối đa là 'N' đơn hàng
        
        return $this->hasMany(DonHang::class,'makh','makh');
    }
}
