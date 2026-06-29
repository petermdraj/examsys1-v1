<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('processed_payments', function (Blueprint $table) {
            $table->string('payment_id')->primary();
            $table->string('gateway', 20);
            $table->uuid('order_id');
            $table->timestamp('processed_at')->useCurrent();
            $table->foreign('order_id')->references('id')->on('orders');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_payments');
    }
};
