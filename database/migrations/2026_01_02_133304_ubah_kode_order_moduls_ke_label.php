<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Produk;
use App\Models\OrderModul;

return new class extends Migration
{
    public function up()
    {
        // Ambil mapping kode numerik → label
        $mapping = Produk::pluck('label', 'kode')->toArray();

        // Update semua data lama: ganti kode numerik jadi label
        OrderModul::chunk(200, function ($orders) use ($mapping) {
            foreach ($orders as $order) {
                $updated = false;
                for ($i = 1; $i <= 5; $i++) {
                    $kolom = 'kode' . $i;
                    $kodeLama = $order->$kolom;
                    if ($kodeLama && isset($mapping[$kodeLama])) {
                        $order->$kolom = $mapping[$kodeLama];
                        $updated = true;
                    }
                }
                if ($updated) {
                    $order->saveQuietly();
                }
            }
        });

        // Opsional: ubah tipe kolom jika perlu (biasanya sudah varchar)
        // Schema::table('order_moduls', function (Blueprint $table) {
        //     for ($i = 1; $i <= 5; $i++) {
        //         $table->string('kode' . $i)->nullable()->change();
        //     }
        // });
    }

    public function down()
    {
        // Jika rollback, sulit karena label → kode numerik tidak unik
        // Jadi tidak perlu down
    }
};