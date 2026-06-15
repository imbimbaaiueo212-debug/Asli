<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('tahap')->nullable()->after('program'); // posisikan sesukamu
        });
    }
    public function down(): void {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('tahap');
        });
    }
};
