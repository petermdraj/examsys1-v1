<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price_monthly', 8, 2)->default(0);
            $table->decimal('price_yearly', 8, 2)->default(0);
            $table->integer('ai_free_generations')->default(10);
            $table->decimal('ai_charge_per_generation', 6, 4)->default(5.0000);
            $table->decimal('commission_rate', 5, 2)->default(20.00);
            $table->integer('max_published_quizzes')->nullable();
            $table->boolean('can_sell_paid_quizzes')->default(false);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('plans'); }
};
