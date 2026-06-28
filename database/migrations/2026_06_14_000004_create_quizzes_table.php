<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('creator_id');
            $table->uuid('category_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->enum('status', ['draft','published','archived','scheduled'])->default('draft');
            $table->enum('visibility', ['public','private','unlisted'])->default('public');
            $table->decimal('price', 8, 2)->default(0.00);
            $table->char('currency', 3)->default('USD');
            $table->integer('duration_minutes')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->integer('max_attempts')->nullable();
            $table->integer('pass_percentage')->default(60);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('show_result_immediately')->default(true);
            $table->boolean('allow_review_after_submit')->default(true);
            $table->boolean('negative_marking_enabled')->default(false);
            $table->boolean('proctoring_enabled')->default(false);
            $table->boolean('certificate_enabled')->default(false);
            $table->json('eligibility_rules')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->integer('total_questions')->default(0);
            $table->decimal('total_marks', 8, 2)->default(0);
            $table->integer('total_attempts')->default(0);
            $table->decimal('average_score', 5, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('creator_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
            $table->index(['status', 'visibility', 'category_id']);
            $table->index(['creator_id', 'status']);
            $table->index(['start_at', 'end_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('quizzes'); }
};
