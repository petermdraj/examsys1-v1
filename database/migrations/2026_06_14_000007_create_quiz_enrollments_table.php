<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('quiz_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quiz_id');
            $table->uuid('user_id');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->enum('source', ['free','purchased','gifted','admin'])->default('free');
            $table->uuid('order_id')->nullable();
            $table->timestamps();
            $table->unique(['quiz_id', 'user_id']);
            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('quiz_enrollments'); }
};
