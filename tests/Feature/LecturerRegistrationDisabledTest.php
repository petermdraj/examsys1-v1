<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\Concerns\HandlesPrivilegedUserFields;
use App\Filament\Admin\Resources\StaffResource\Pages\CreateStaff;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LecturerRegistrationDisabledTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_register_routes_are_not_registered(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('register'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('register.post'));
    }

    public function test_public_register_url_returns_not_found(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }

    public function test_filament_lecturer_register_url_returns_not_found(): void
    {
        $this->get('/lecturer/register')->assertNotFound();
    }

    public function test_admin_can_create_lecturer_via_staff_resource_logic(): void
    {
        $superAdmin = User::factory()->create([
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin);

        $page = new class extends CreateStaff
        {
            use HandlesPrivilegedUserFields;

            public function createLecturer(array $data): User
            {
                return $this->createUserWithPrivilegedFields($data);
            }
        };

        $lecturer = $page->createLecturer([
            'name'                      => 'Created Lecturer',
            'email'                     => 'created-lecturer@example.com',
            'password'                  => bcrypt('password123'),
            'role'                      => 'lecturer',
            'is_active'                 => true,
            'ai_credits_free_remaining' => 10,
        ]);

        $this->assertSame('lecturer', $lecturer->role);
        $this->assertTrue($lecturer->hasRole('lecturer'));
        $this->assertDatabaseHas('users', [
            'email' => 'created-lecturer@example.com',
            'role'  => 'lecturer',
        ]);
    }
}
