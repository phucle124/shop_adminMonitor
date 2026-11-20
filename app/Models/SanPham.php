<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */

class SanPham extends Model
{
    use HasFactory;
    // Tên bảng tương ứng trong DB (nếu khác chuẩn)
    protected $table = 'sanpham';

    // Khóa chính của bảng (Mặc định là id)
    protected $primaryKey = 'masp';

    // Các cột cho phép ghi dữ liệu hàng loạt (Mass Assignment)
    protected $fillable = [
        // 'ten_cot',
        'tensp',
        'mota',
        'dongia',
        'soluong_tonkho',
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

    public function chitiet_donhangs(){ //Có tối đa 'N' đơn hàng
        return $this->hasMany(ChiTiet_DonHang::class,'masp','masp');//cột FK 'masp' ở bảng chitiet_dh
    }
}
