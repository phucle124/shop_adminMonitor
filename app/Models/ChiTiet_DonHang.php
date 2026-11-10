<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTiet_DonHang extends Model
{
    // Tên bảng tương ứng trong DB (nếu khác chuẩn)
    protected $table = 'chitiet_donhang';

    // Khóa chính của bảng (Mặc định là id)
    protected $primaryKey = 'madh_chitiet';

    // Các cột cho phép ghi dữ liệu hàng loạt (Mass Assignment)
    protected $fillable = [//Mass PHAI trung voi cac column tren database
        // 'ten_cot',
        'giaban',
        'soluong_mua',
        'madh', // FK
        'masp', // FK
        
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




    // 2 Phuơng thức tương ứng 2 cột FK ở mass
    public function donhang(){//Thuộc về 1 đơn hàng
        return $this->belongsTo(DonHang::class,'madh','madh');//cột FK 'madh' đang ở bảng này
    }

    public function sanpham(){//Thuộc về 1 sản phẩm
        return $this->belongsTo(SanPham::class,'masp','masp');//cột FK 'masp' đang ở bảng này
    }

}
