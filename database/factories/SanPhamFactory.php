<?php

namespace Database\Factories;

use App\Models\SanPham;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SanPham>
 */
class SanPhamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $dssp = [
                [
                    'tensp' => 'Samsung Galaxy A55 5G (8GB | 128GB)',
                    'mota' => 'Trang bị chip Exynos 1480 tiến trình 4nm kiến trúc ARM tích hợp sẵn NPU. Màn hình Super AMOLED 120Hz, kháng nước chuẩn IP67 và viên pin 5.000mAh.',
                    'dongia' => 8150000,
                    'soluong_tonkho' => 30,
                ],
                [
                    'tensp' => 'iPhone 15 Pro Max (256GB)',
                    'mota' => 'Thiết kế titan bền bỉ, chip A17 Pro mạnh mẽ, hệ thống camera cực nét và cổng USB-C tốc độ cao.',
                    'dongia' => 29990000,
                    'soluong_tonkho' => 15,
                ],
                [
                    'tensp' => 'Xiaomi Redmi Note 13 Pro (8GB | 256GB)',
                    'mota' => 'Camera siêu phân giải 200MP, màn hình AMOLED 120Hz viền siêu mỏng, sạc nhanh 67W.',
                    'dongia' => 6490000,
                    'soluong_tonkho' => 50,
                ],
                [
                    'tensp' => 'Masstel IZI 10',
                    'mota' => 'là 1 phiên bản "cục gạch" khác của nhà Masstel có tích hợp 4G, mang thiết kế của chiếc điện thoại phổ thông với màn hình 262.000 màu 1.77 inch, độ phân giải 128 x 160 Pixels, Tích hợp đèn pin chiếu sáng tốt, ánh sáng mạnh mẽ, có thể dùng thay thế đèn pin nhỏ',
                    'dongia' => 430.000,
                    'soluong_tonkho' => 20,
                ],
                [
                    'tensp' => 'Nokia 215 4G',
                    'mota' => 'sử dụng chất liệu nhựa cho kiểu dáng bền bỉ, trang bị màn hình TFT LCD rộng 2.4 inch có độ phân giải 240 x 320 pixels, Với pin 1150 mAh, việc sử dụng được 2 - 3 ngày là điều mà Nokia 215 4G có thể làm được.',
                    'dongia' => 980000,
                    'soluong_tonkho' => 30,
                ],
        ];
        return $this->faker->randomElement($dssp); 
    }
}
