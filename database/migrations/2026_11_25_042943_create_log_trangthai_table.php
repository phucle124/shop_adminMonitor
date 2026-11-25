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
        Schema::create('log_trangthai', function (Blueprint $table) {
            $table->id('malog_tt')->autoIncrement();
            $table->string('ma_doituong',50)->nullable(); //format thành DH... hoặc GDQR...
            $table->enum('loai_doituong',['DON_HANG','GIAO_DICH_QR'])->nullable();
            $table->string('trangthai_dh',50)->nullable();
            $table->string('trangthai_tt',50)->nullable();
            $table->timestamps();
            
            // Heavy Write => [Light Read] cho yêu cầu của "nghiệp vụ đọc log"
            $table->index(['loai_doituong', 'ma_doituong'], 'idx_log_loai_ma');
            $table->index('created_at', 'idx_log_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_trangthai');
    }
};
