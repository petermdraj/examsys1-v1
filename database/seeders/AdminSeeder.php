<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@quiz.com'],
            [
                'name'     => 'ExamSys Admin',
                'password' => bcrypt('password'),
            ]
        );

        $admin->forceFill([
            'role'                      => 'super_admin',
            'is_active'                 => true,
            'ai_credits_free_remaining' => 999,
            'email_verified_at'         => now(),
        ])->save();

        $admin->assignRole('super_admin');
    }
}
