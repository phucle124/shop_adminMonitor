<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('donhang', function (Blueprint $table) {
            $table->id('madh')->autoIncrement();
            $table->double('tongtien');
            $table->enum('trangthai_donhang',['CHO_XAC_NHAN','DA_XAC_NHAN','DA_HUY'])->default('CHO_XAC_NHAN');
            $table->enum('trangthai_thanhtoan',['CHUA_THANH_TOAN','DA_THANH_TOAN'])->default('CHUA_THANH_TOAN');
            $table->unsignedBigInteger('makh');
            $table->foreign('makh')->references('makh')->on('khachhang');

            //Thêm 3 cột  => phục vụ timer 15ph đếm ngược
            $table->dateTime('qr_created_at')->nullable()->default(null);
            $table->dateTime('qr_expired_at')->nullable()->default(null);
            $table->dateTime('paid_at')->nullable()->default(null);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donhang');
    }
};
