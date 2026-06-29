<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('processed_payments');

        if (Schema::hasTable('quiz_enrollments') && Schema::hasColumn('quiz_enrollments', 'order_id')) {
            Schema::table('quiz_enrollments', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            });
        }

        Schema::dropIfExists('orders');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('creator_payouts');
        Schema::dropIfExists('plans');

        if (Schema::hasTable('quiz_enrollments')) {
            DB::table('quiz_enrollments')->where('source', 'purchased')->update(['source' => 'free']);
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                foreach (['wallet_balance', 'payout_gateway', 'payout_details'] as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('quizzes')) {
            Schema::table('quizzes', function (Blueprint $table) {
                foreach (['price', 'currency'] as $col) {
                    if (Schema::hasColumn('quizzes', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        foreach (['quizzes', 'questions', 'question_collections'] as $table) {
            $this->renameCreatorIdColumn($table);
        }

        if (Schema::hasTable('users')) {
            DB::table('users')->where('role', 'customer')->update(['role' => 'student']);
            DB::table('users')->where('role', 'creator')->update(['role' => 'lecturer']);

            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','lecturer','student') NOT NULL DEFAULT 'student'");
            }
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'customer')->update(['name' => 'student']);
            DB::table('roles')->where('name', 'creator')->update(['name' => 'lecturer']);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Irreversible — restore from backup if needed.
    }

    private function renameCreatorIdColumn(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'lecturer_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropForeign(['lecturer_id']);
        });

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->renameColumn('lecturer_id', 'lecturer_id');
        });

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->foreign('lecturer_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
