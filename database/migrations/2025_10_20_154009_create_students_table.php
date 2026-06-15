<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('students', function (Blueprint $t) {
            $t->id();
            $t->string('nim', 20)->unique();               // identitas murid
            $t->string('nama');
            $t->string('kelas')->nullable();
            $t->date('tgl_lahir')->nullable();
            $t->unsignedTinyInteger('usia')->nullable();
            $t->string('orangtua')->nullable();
            $t->string('no_telp', 20)->nullable();
            $t->text('alamat')->nullable();
            $t->string('guru_wali')->nullable();
            $t->enum('source', ['trial','direct']);        // asal data
            $t->foreignId('murid_trial_id')
              ->nullable()
              ->constrained('murid_trials')
              ->nullOnDelete();
            $t->timestamp('promoted_at')->nullable();
            $t->timestamps();

            $t->index(['nama','kelas']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('students');
    }
};
