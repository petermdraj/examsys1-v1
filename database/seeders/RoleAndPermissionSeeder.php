<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear the Spatie permission cache so fresh roles/permissions take effect
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ────────────────────────────────────────────────────
        $permissions = [
            // Quiz
            'quiz.create', 'quiz.edit', 'quiz.delete', 'quiz.publish',
            'quiz.view_reports',

            // Questions
            'question.create', 'question.edit', 'question.delete',
            'question.bulk_generate',

            // AI
            'ai.generate_quiz', 'ai.unlimited',

            // Student
            'quiz.attempt', 'certificate.download',

            // Admin
            'admin.manage_users', 'admin.manage_settings',
            'admin.view_all_reports', 'admin.monitor_live_exams',
            'admin.send_bulk_notifications',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Roles ──────────────────────────────────────────────────────────
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $creator    = Role::firstOrCreate(['name' => 'lecturer',     'guard_name' => 'web']);
        $customer   = Role::firstOrCreate(['name' => 'student',    'guard_name' => 'web']);

        // ── Role → Permission assignments ──────────────────────────────────
        $superAdmin->syncPermissions(Permission::all());

        $creator->syncPermissions([
            'quiz.create', 'quiz.edit', 'quiz.delete', 'quiz.publish',
            'quiz.view_reports', 'question.create', 'question.edit',
            'question.delete', 'question.bulk_generate',
            'ai.generate_quiz',
            'quiz.attempt', 'certificate.download',
        ]);

        $customer->syncPermissions([
            'quiz.attempt', 'certificate.download',
        ]);

        // Clear again after seeding so the app picks up the new values immediately
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
