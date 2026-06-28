<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();        // e.g. attempt_result, purchase_receipt
            $table->string('name');                 // Human label shown in admin
            $table->string('subject');
            $table->longText('body');               // Full HTML
            $table->json('variables')->nullable();  // [{name:'user_name',description:'...'}]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
