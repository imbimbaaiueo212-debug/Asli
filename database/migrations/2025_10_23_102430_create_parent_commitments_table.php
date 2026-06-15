<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parent_commitments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('murid_trial_id')->constrained('murid_trials')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();

            $table->string('parent_name')->nullable();
            $table->string('child_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();

            $table->boolean('agreed')->default(false);
            $table->timestamp('signed_at')->nullable();

            $table->timestamps();
            $table->unique(['murid_trial_id']); // satu komitmen per trial
        });
    }
    public function down(): void {
        Schema::dropIfExists('parent_commitments');
    }
};
