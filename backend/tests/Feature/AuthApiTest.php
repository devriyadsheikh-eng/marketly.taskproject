<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['auth:sanctum', 'active', 'role:admin'])
            ->get('/api/__test/admin', fn () => response()->json(['ok' => true]));
        Route::middleware(['auth:sanctum', 'active', 'role:shop_owner'])
            ->get('/api/__test/shop-owner', fn () => response()->json(['ok' => true]));
    }

    public function test_private_endpoints_return_401_without_authentication(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
        $this->postJson('/api/logout')->assertUnauthorized();
    }

    public function test_cors_preflight_and_sanctum_csrf_cookie_are_available_to_the_spa(): void
    {
        $origin = 'http://localhost:5173';

        $this->options('/api/login', [], [
            'Origin' => $origin,
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'content-type,x-xsrf-token',
        ])->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', $origin)
            ->assertHeader('Access-Control-Allow-Credentials', 'true');

        $this->get('/sanctum/csrf-cookie', [
            'Origin' => $origin,
            'Referer' => $origin.'/',
        ])->assertNoContent()->assertHeader('Set-Cookie');
    }

    public function test_login_validates_credentials_and_rejects_inactive_accounts(): void
    {
        $user = User::factory()->shopOwner()->create(['password' => 'correct-password']);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $inactive = User::factory()->admin()->create([
            'email' => 'inactive-admin@example.com',
            'password' => 'correct-password',
            'is_active' => false,
        ]);

        $this->postJson('/api/login', ['email' => $inactive->email, 'password' => 'correct-password'])
            ->assertForbidden()
            ->assertJsonPath('code', 'ACCOUNT_INACTIVE');
    }

    public function test_login_is_rate_limited(): void
    {
        $payload = ['email' => 'missing-user@example.com', 'password' => 'not-a-valid-password'];

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->postJson('/api/login', $payload)->assertUnprocessable();
        }

        $this->postJson('/api/login', $payload)->assertTooManyRequests();
    }

    public function test_bearer_token_login_user_and_logout_work_without_exposing_password(): void
    {
        $user = User::factory()->shopOwner()->create(['password' => 'correct-password']);

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'correct-password',
            'role' => User::ROLE_ADMIN,
        ])->assertOk()->assertJsonPath('user.role', User::ROLE_SHOP_OWNER);

        $this->assertArrayNotHasKey('password', $login->json('user'));
        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $user->id]);
        $token = $login->json('token');

        $this->withToken($token)->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonMissingPath('user.password');

        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
        Auth::forgetGuards();
        $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
    }

    public function test_stateful_sanctum_login_uses_the_session_and_logout_invalidates_it(): void
    {
        $user = User::factory()->admin()->create(['password' => 'correct-password']);
        $this->withHeaders([
            'Origin' => 'http://localhost:5173',
            'Referer' => 'http://localhost:5173/',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertOk();

        $this->assertArrayNotHasKey('token', $login->json());
        $this->assertAuthenticatedAs($user, 'web');
        $this->getJson('/api/user')->assertOk()->assertJsonPath('user.role', User::ROLE_ADMIN);
        $this->postJson('/api/logout')->assertOk();
        Auth::forgetGuards();
        $this->assertGuest('web');
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_role_middleware_rejects_the_wrong_role_and_inactive_users(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->shopOwner()->create();
        $ownerToken = $owner->createToken('test')->plainTextToken;
        $adminToken = $admin->createToken('test')->plainTextToken;

        $this->withToken($adminToken)->getJson('/api/__test/admin')->assertOk();
        Auth::forgetGuards();
        $this->withToken($ownerToken)->getJson('/api/__test/admin')->assertForbidden();
        Auth::forgetGuards();
        $this->withToken($ownerToken)->getJson('/api/__test/shop-owner')->assertOk();
        Auth::forgetGuards();
        $this->withToken($adminToken)->getJson('/api/__test/shop-owner')->assertForbidden();
    }

    public function test_inactive_users_with_valid_tokens_are_blocked(): void
    {
        $owner = User::factory()->shopOwner()->create(['is_active' => false]);
        $this->assertDatabaseHas('users', ['id' => $owner->id, 'is_active' => false]);
        $ownerToken = $owner->createToken('test')->plainTextToken;

        $this->withToken($ownerToken)->getJson('/api/__test/shop-owner')
            ->assertForbidden()
            ->assertJsonPath('code', 'ACCOUNT_INACTIVE');
    }
}
