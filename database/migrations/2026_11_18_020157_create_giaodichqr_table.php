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
        Schema::create('giaodichqr', function (Blueprint $table) {
            $table->id('ma_giao_dich')->autoIncrement();
            $table->string('ma_tra_cuu_qr')->nullable(); //qrCode
            $table->decimal('sotien',15,2);           //amount
            $table->string('qr_url_pic')->nullable();
            $table->string('noi_dung_ck')->default('Nội dung chuyển khoản mặc định');   //content
            $table->string('stk_ngan_hang');    //bankAccount
            $table->string('ma_ngan_hang')->default('BIDV');        //bankCode
            $table->enum('trang_thai_qr',['SUCCESS','FAILED'])->nullable();      //Response->status

            $table->unsignedBigInteger('madh');
            $table->foreign('madh')->references('madh')->on('donhang');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giaodichqr');
    }
};
