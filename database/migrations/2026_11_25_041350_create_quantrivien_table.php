<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quantrivien', function (Blueprint $table) {
            $table->id('maqt');
            $table->string('hoten',50);
            $table->string('email',50)->unique();
            $table->string('matkhau',100);

            $table->timestamps();
        });

        DB::table('quantrivien')->insert([
            [
                'maqt'       => 1,
                'hoten'      => 'Phuc',
                'email'      => 'phuc95721@gmail.com',
                'matkhau'    => Hash::make('taolatao123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quantrivien');
    }
};
