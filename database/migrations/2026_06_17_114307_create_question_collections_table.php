<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('question_collections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lecturer_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable();
            $table->timestamps();
            $table->index('lecturer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_collections');
    }
};
