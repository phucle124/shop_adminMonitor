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
            $table->id('madh');
            $table->double('tongtien');
            $table->enum('trangthai',['CHO_THANH_TOAN','DA_THANH_TOAN']);
            
            $table->unsignedBigInteger('makh');
            $table->foreign('makh')->references('makh')->on('khachhang');
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
