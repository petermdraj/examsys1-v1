<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\Settings;
use App\Filament\Admin\Resources\StaffResource;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_admin_role_can_access_admin_panel(): void
    {
        $admin = $this->makeAdminUser();

        $this->assertTrue($admin->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_admin_role_cannot_access_settings_page(): void
    {
        $admin = $this->makeAdminUser();

        $this->actingAs($admin);

        $this->assertFalse(Settings::canAccess());
    }

    public function test_super_admin_can_access_settings_page(): void
    {
        $superAdmin = $this->makeSuperAdminUser();

        $this->actingAs($superAdmin);

        $this->assertTrue(Settings::canAccess());
    }

    public function test_admin_cannot_edit_or_delete_super_admin_staff(): void
    {
        $admin = $this->makeAdminUser();
        $superAdmin = $this->makeSuperAdminUser();

        $this->actingAs($admin);

        $this->assertFalse(StaffResource::canEdit($superAdmin));
        $this->assertFalse(StaffResource::canDelete($superAdmin));
    }

    public function test_admin_cannot_assign_super_admin_role(): void
    {
        $admin = $this->makeAdminUser();

        $this->actingAs($admin);

        $this->assertArrayNotHasKey('super_admin', User::assignableStaffRoleOptions());
    }

    public function test_super_admin_can_assign_super_admin_role(): void
    {
        $superAdmin = $this->makeSuperAdminUser();

        $this->actingAs($superAdmin);

        $this->assertArrayHasKey('super_admin', User::assignableStaffRoleOptions());
    }

    private function makeAdminUser(): User
    {
        $user = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);
        $user->assignRole('admin');

        return $user;
    }

    private function makeSuperAdminUser(): User
    {
        $user = User::factory()->create([
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
        $user->assignRole('super_admin');

        return $user;
    }
}
