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
        Schema::create('khachhang', function (Blueprint $table) {
            $table->id('makh');
            $table->string('hoten',50);
            $table->string('email',50)->unique()->nullable();
            $table->string('sdt',10)->unique()->nullable();
            $table->string('matkhau',100);
            $table->string('diachi',100)->nullable();
            $table->enum('gioitinh',['NAM','NU'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('khachhang');
    }
};
