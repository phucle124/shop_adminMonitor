<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonHang extends Model
{
    // Tên bảng tương ứng trong DB (nếu khác chuẩn)
    protected $table = 'donhang';

    // Khóa chính của bảng (Mặc định là id)
    protected $primaryKey = 'madh';

    // Các cột cho phép ghi dữ liệu hàng loạt (Mass Assignment)
    protected $fillable = [
        // 'ten_cot',
        'tongtien',
        'trangthai',
        'makh', //Luu y: PHẢI gán FK (từ cột data trên db thực tế) vào mass luôn
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




    public function khachhang(){ //Thuộc về chỉ '1' Khách hàng
        return $this->belongsTo(KhachHang::class,'makh','makh');//cột FK 'makh' đang ở bảng này
    }

    // 1 Phương thức tương ứng 1 cột FK ở mass
    public function chitiet_donhangs(){ //Có tối đa 'N' chi tiết đơn hàng 
        return $this->hasMany(ChiTiet_DonHang::class,'madh','madh'); //cột FK 'madh' ở bảng chitiet_dh
    }
}
