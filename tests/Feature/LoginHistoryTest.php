<?php

namespace Tests\Feature;

use App\Models\LoginHistory;
use App\Models\User;
use App\Services\Auth\LoginHistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class LoginHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_records_history_entry(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        app(LoginHistoryService::class)->record($user, Request::create('/login', 'POST', [], [], [], [
            'REMOTE_ADDR'     => '192.168.1.10',
            'HTTP_USER_AGENT' => 'TestBrowser/1.0',
        ]));

        $this->assertDatabaseHas('login_histories', [
            'user_id'    => $user->id,
            'ip_address' => '192.168.1.10',
        ]);

        $this->assertEquals(1, LoginHistory::count());
    }
}
