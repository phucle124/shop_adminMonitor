<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Destroy_Model_Controller extends Command
{
    /**
     * Execute the console command.
     */

    protected $signature = 'make:destroy {name}';
    protected $description = 'Đây là đoạn mã lệnh tự viết dùng để xoá nhanh model và controller
                                        theo cú pháp: 

                                                    php artisan make:destroy [ten_model_da_tao]
                                        ';

    // protected $hidden = true;
    // protected $isolatedExitCode=300;

    public function handle()
    {
        //Khai báo
        $name = $this->argument('name');
        $studly = Str::studly($name); // Ví dụ: KhachHang
        
        
        if ($this->confirm("Có muốn xóa Controller kèm theo luôn không?", true)) {
            // Thực hiện xóa cả Model và Controller
            $this->deleteFile(app_path("Models/{$studly}.php"), "Model");
            $this->deleteFile(app_path("Http/Controllers/{$studly}Controller.php"), "Controller");
        } else {
            // Chỉ xóa duy nhất Model
            $this->deleteFile(app_path("Models/{$studly}.php"), "Model");
        }

        // Xóa Requests (nếu có)
        // $this->deleteFile(app_path("Http/Requests/Store{$studly}Request.php"), "Store Request");
        // $this->deleteFile(app_path("Http/Requests/Update{$studly}Request.php"), "Update Request");

        // Xóa Factory & Seeder
        // $this->deleteFile(database_path("factories/{$studly}Factory.php"), "Factory");
        // $this->deleteFile(database_path("seeders/{$studly}Seeder.php"), "Seeder");

        // Xóa Migration (Tìm file chứa tên bảng)
        // $migrations = File::glob(database_path("migrations/*_create_{$studly}_table.php"));
        // foreach ($migrations as $migration) {
        //     $this->deleteFile($migration, "Migration");
        // }

        $this->info("Đã xoá xong model {$studly}!");
        return Command::SUCCESS;
    }

    private function deleteFile($path, $type)
    {
        if (File::exists($path)) {
            File::delete($path);
            $this->line("<info>✔</info> Đã xóa {$type}: " . basename($path));
        }
    }
    

}
