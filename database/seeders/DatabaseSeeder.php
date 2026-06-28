<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Core seeders — always run (no interactive prompts; demo data is
        // handled separately by the installer's step 8 via the demo seeders).
        // RoleAndPermissionSeeder MUST run first — roles are required by
        // AdminSeeder (assignRole) and all demo seeders.
        // CategorySeeder is intentionally omitted here.
        // Demo installs create their own focused categories via the demo seeders.
        // Fresh (non-demo) installs start with no categories — admins create them.
        $this->call([
            RoleAndPermissionSeeder::class,
            AdminSeeder::class,
            PlanSeeder::class,
            EmailTemplateSeeder::class,   // system default mail templates — always seeded
        ]);
    }
}
