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
        Schema::create('chitiet_donhang', function (Blueprint $table) {
            $table->id('madh_chitiet');
            $table->double('giaban');
            $table->unsignedSmallInteger('soluong_mua');

            $table->unsignedBigInteger('madh');
            $table->unsignedBigInteger('masp');
            $table->foreign('madh')->references('madh')->on('donhang');
            $table->foreign('masp')->references('masp')->on('sanpham');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chitiet_donhang');
    }
};
