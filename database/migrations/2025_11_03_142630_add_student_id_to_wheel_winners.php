<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudentIdToWheelWinners extends Migration
{
    public function up()
    {
        Schema::table('wheel_winners', function (Blueprint $table) {
            // nullable karena beberapa pemenang mungkin bukan dari tabel students
            $table->unsignedBigInteger('student_id')->nullable()->after('row_hash')->index();

            // jika ingin foreign key (opsional — uncomment kalau mau)
            // $table->foreign('student_id')->references('id')->on('students')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('wheel_winners', function (Blueprint $table) {
            // jika foreign key dipakai, drop dulu
            // $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });
    }
}
