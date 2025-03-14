<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;
use PragmaRX\Google2FA\Google2FA;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    protected $google2faMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and bind the Google2FA mock
        $this->google2faMock = Mockery::mock(Google2FA::class);
        $this->app->instance(Google2FA::class, $this->google2faMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test user can enable 2FA.
     */
    public function test_user_can_enable_2fa()
    {
        // Create a user
        /** @var User $user */
        $user = User::factory()->create();

        // Setup expectations for the mock
        $this->google2faMock->shouldReceive('generateSecretKey')
            ->once()
            ->andReturn('JBSWY3DPEHPK3PXP');

        $this->google2faMock->shouldReceive('getQRCodeUrl')
            ->once()
            ->with(
                config('app.name'),
                $user->email,
                'JBSWY3DPEHPK3PXP'
            )
            ->andReturn('https://chart.googleapis.com/chart?test-qr-code');

        // Make the request to enable 2FA
        $response = $this->actingAs($user)
            ->postJson('/api/v1/2fa/enable');

        $response->assertStatus(200)
            ->assertJson([
                'message' => '2FA setup initiated'
            ])
            ->assertJsonStructure([
                'message',
                'secret',
                'qr_code',
                'recovery_codes'
            ]);

        // Verify secret was stored
        $user->refresh();
        $this->assertEquals('JBSWY3DPEHPK3PXP', $user->two_factor_secret);
        $this->assertIsArray($user->two_factor_recovery_codes);
        $this->assertCount(8, $user->two_factor_recovery_codes);
    }

    /**
     * Test 2FA confirmation with valid code.
     */
    public function test_user_can_confirm_2fa_with_valid_code()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_enabled' => false,
            'two_factor_recovery_codes' => json_encode(['code1', 'code2']),
            'two_factor_confirmed_at' => null
        ]);

        // Setup expectations for the mock
        $this->google2faMock->shouldReceive('verifyKey')
            ->once()
            ->with('JBSWY3DPEHPK3PXP', '123456')
            ->andReturn(true);

        // Make the request to confirm 2FA
        $response = $this->actingAs($user)
            ->postJson('/api/v1/2fa/confirm', [
                'code' => '123456'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => '2FA enabled successfully'
            ]);

        // Verify that 2FA is now enabled
        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
        $this->assertNotNull($user->two_factor_confirmed_at);
    }

    /**
     * Test 2FA confirmation fails with invalid code.
     */
    public function test_2fa_confirmation_fails_with_invalid_code()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_enabled' => false,
            'two_factor_recovery_codes' => json_encode(['code1', 'code2']),
            'two_factor_confirmed_at' => null
        ]);

        // Setup expectations for the mock
        $this->google2faMock->shouldReceive('verifyKey')
            ->once()
            ->with('JBSWY3DPEHPK3PXP', '123456')
            ->andReturn(false);

        // Make the request to confirm 2FA with invalid code
        $response = $this->actingAs($user)
            ->postJson('/api/v1/2fa/confirm', [
                'code' => '123456'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid verification code'
            ]);

        // Verify that 2FA is still not enabled
        $user->refresh();
        $this->assertFalse($user->two_factor_enabled);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    /**
     * Test verification with valid 2FA code.
     */
    public function test_2fa_verification_with_valid_code()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => json_encode(['code1', 'code2']),
            'two_factor_confirmed_at' => now()
        ]);

        // Setup expectations for the mock
        $this->google2faMock->shouldReceive('verifyKey')
            ->once()
            ->with('JBSWY3DPEHPK3PXP', '123456')
            ->andReturn(true);

        // Make the request to verify 2FA
        $response = $this->actingAs($user)
            ->postJson('/api/v1/2fa/verify', [
                'code' => '123456'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => '2FA verified successfully'
            ]);

        // Check session flag was set (in a real app, this would be used for subsequent requests)
        $this->assertTrue(session()->has('2fa_verified'));
    }

    /**
     * Test verification with valid recovery code.
     */
    public function test_2fa_verification_with_valid_recovery_code()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => ['recovery-code-1', 'recovery-code-2'],
            'two_factor_confirmed_at' => now()
        ]);

        // Make the request to verify 2FA with recovery code
        $response = $this->actingAs($user)
            ->postJson('/api/v1/2fa/verify', [
                'code' => 'recovery-code-1'
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Recovery code accepted')
            ->assertJsonPath('remaining_codes', 1);

        // Check that the used recovery code is removed
        $user->refresh();
        $this->assertCount(1, $user->two_factor_recovery_codes);
        $this->assertNotContains('recovery-code-1', $user->two_factor_recovery_codes);
        $this->assertContains('recovery-code-2', $user->two_factor_recovery_codes);

        // Check session flag was set
        $this->assertTrue(session()->has('2fa_verified'));
    }

    /**
     * Test verification fails with invalid code.
     */
    public function test_2fa_verification_fails_with_invalid_code()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => json_encode(['code1', 'code2']),
            'two_factor_confirmed_at' => now()
        ]);

        // Setup expectations for the mock
        $this->google2faMock->shouldReceive('verifyKey')
            ->once()
            ->with('JBSWY3DPEHPK3PXP', '123456')
            ->andReturn(false);

        // Make the request to verify 2FA with invalid code
        $response = $this->actingAs($user)
            ->postJson('/api/v1/2fa/verify', [
                'code' => '123456'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid verification code'
            ]);

        // Check session flag was not set
        $this->assertFalse(session()->has('2fa_verified'));
    }

    /**
     * Test verification fails with invalid recovery code.
     */
    public function test_2fa_verification_fails_with_invalid_recovery_code()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => ['valid-code-1', 'valid-code-2'],
            'two_factor_confirmed_at' => now()
        ]);

        // Make the request to verify 2FA with invalid recovery code
        $response = $this->actingAs($user)
            ->postJson('/api/v1/2fa/verify', [
                'code' => 'invalid-recovery-code'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid recovery code'
            ]);

        // Check session flag was not set
        $this->assertFalse(session()->has('2fa_verified'));
    }

    /**
     * Test disabling 2FA with valid code.
     */
    public function test_user_can_disable_2fa_with_valid_code()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => json_encode(['code1', 'code2']),
            'two_factor_confirmed_at' => now()
        ]);

        // Setup expectations for the mock
        $this->google2faMock->shouldReceive('verifyKey')
            ->once()
            ->with('JBSWY3DPEHPK3PXP', '123456')
            ->andReturn(true);

        // Make the request to disable 2FA
        $response = $this->actingAs($user)
            ->postJson('/api/v1/2fa/disable', [
                'code' => '123456'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => '2FA disabled successfully'
            ]);

        // Verify that 2FA is now disabled and data is cleared
        $user->refresh();
        $this->assertFalse($user->two_factor_enabled);
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    /**
     * Test disabling 2FA fails with invalid code.
     */
    public function test_disabling_2fa_fails_with_invalid_code()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => json_encode(['code1', 'code2']),
            'two_factor_confirmed_at' => now()
        ]);

        // Setup expectations for the mock
        $this->google2faMock->shouldReceive('verifyKey')
            ->once()
            ->with('JBSWY3DPEHPK3PXP', '123456')
            ->andReturn(false);

        // Make the request to disable 2FA with invalid code
        $response = $this->actingAs($user)
            ->postJson('/api/v1/2fa/disable', [
                'code' => '123456'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid verification code'
            ]);

        // Verify that 2FA is still enabled
        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
    }
}
