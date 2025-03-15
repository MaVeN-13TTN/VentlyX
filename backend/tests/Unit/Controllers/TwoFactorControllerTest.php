<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Http\Controllers\API\TwoFactorController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;
use PragmaRX\Google2FA\Google2FA;
use ReflectionClass;

class TwoFactorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;
    protected $google2faMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock for Google2FA
        $this->google2faMock = Mockery::mock(Google2FA::class);

        // Create controller with the mock injected
        $this->controller = new TwoFactorController($this->google2faMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test enabling 2FA initiates the setup process.
     */
    public function test_enable_initiates_two_factor_setup()
    {
        $user = User::factory()->create([
            'two_factor_enabled' => false,
            'two_factor_secret' => null
        ]);

        // Updated to use correct API versioning
        $request = Request::create('/api/v1/2fa/enable', 'POST');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Setup expectations for the Google2FA mock
        $this->google2faMock->shouldReceive('generateSecretKey')
            ->once()
            ->andReturn('test-secret-key');

        $this->google2faMock->shouldReceive('getQRCodeUrl')
            ->once()
            ->withArgs(function ($companyName, $email, $secret) use ($user) {
                return $companyName === config('app.name')
                    && $email === $user->email
                    && $secret === 'test-secret-key';
            })
            ->andReturn('https://chart.googleapis.com/chart?test-qr-code');

        // Call the method
        $response = $this->controller->enable($request);
        $responseData = json_decode($response->getContent(), true);

        // Assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('2FA setup initiated', $responseData['message']);
        $this->assertEquals('test-secret-key', $responseData['secret']);
        $this->assertArrayHasKey('qr_code', $responseData);
        $this->assertArrayHasKey('recovery_codes', $responseData);

        // Verify user was updated
        $user = $user->fresh();
        $this->assertEquals('test-secret-key', $user->two_factor_secret);
        $this->assertIsArray($user->two_factor_recovery_codes);
        $this->assertCount(8, $user->two_factor_recovery_codes);
    }

    /**
     * Test enabling 2FA fails if already enabled.
     */
    public function test_enable_fails_if_already_enabled()
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'existing-secret',
            'two_factor_confirmed_at' => now()
        ]);

        // Updated to use correct API versioning
        $request = Request::create('/api/v1/2fa/enable', 'POST');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = $this->controller->enable($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('2FA is already enabled', $responseData['message']);
    }

    /**
     * Test confirming 2FA with valid code activates it.
     */
    public function test_confirm_activates_two_factor_with_valid_code()
    {
        $user = User::factory()->create([
            'two_factor_enabled' => false,
            'two_factor_secret' => 'test-secret-key',
            'two_factor_recovery_codes' => ['code1', 'code2'],
            'two_factor_confirmed_at' => null
        ]);

        // Updated to use correct API versioning
        $request = Request::create('/api/v1/2fa/confirm', 'POST', [
            'code' => '123456'
        ]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Setup expectations for the Google2FA mock
        $this->google2faMock->shouldReceive('verifyKey')
            ->once()
            ->with('test-secret-key', '123456')
            ->andReturn(true);

        $response = $this->controller->confirm($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('2FA enabled successfully', $responseData['message']);

        // Verify user was updated
        $user = $user->fresh();
        $this->assertTrue($user->two_factor_enabled);
        $this->assertNotNull($user->two_factor_confirmed_at);
    }

    /**
     * Test confirming 2FA with invalid code fails.
     */
    public function test_confirm_fails_with_invalid_code()
    {
        $user = User::factory()->create([
            'two_factor_enabled' => false,
            'two_factor_secret' => 'test-secret-key',
            'two_factor_confirmed_at' => null
        ]);

        // Updated to use correct API versioning
        $request = Request::create('/api/v1/2fa/confirm', 'POST', [
            'code' => '123456'
        ]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Setup expectations for the Google2FA mock
        $this->google2faMock->shouldReceive('verifyKey')
            ->once()
            ->with('test-secret-key', '123456')
            ->andReturn(false);

        $response = $this->controller->confirm($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Invalid verification code', $responseData['message']);

        // Verify user was not updated
        $user = $user->fresh();
        $this->assertFalse($user->two_factor_enabled);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    /**
     * Test disabling 2FA works with valid code.
     */
    public function test_disable_works_with_valid_code()
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'test-secret-key',
            'two_factor_recovery_codes' => ['code1', 'code2'],
            'two_factor_confirmed_at' => now()
        ]);

        // Updated to use correct API versioning
        $request = Request::create('/api/v1/2fa/disable', 'POST', [
            'code' => '123456'
        ]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Setup expectations for the Google2FA mock
        $this->google2faMock->shouldReceive('verifyKey')
            ->once()
            ->with('test-secret-key', '123456')
            ->andReturn(true);

        $response = $this->controller->disable($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('2FA disabled successfully', $responseData['message']);

        // Verify user was updated
        $user = $user->fresh();
        $this->assertFalse($user->two_factor_enabled);
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    /**
     * Test disabling 2FA fails if not enabled.
     */
    public function test_disable_fails_if_not_enabled()
    {
        $user = User::factory()->create([
            'two_factor_enabled' => false
        ]);

        // Updated to use correct API versioning
        $request = Request::create('/api/v1/2fa/disable', 'POST', [
            'code' => '123456'
        ]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = $this->controller->disable($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('2FA is not enabled', $responseData['message']);
    }

    /**
     * Test verification with valid TOTP code succeeds.
     */
    public function test_verify_succeeds_with_valid_totp_code()
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'test-secret-key',
            'two_factor_confirmed_at' => now()
        ]);

        // Updated to use correct API versioning
        $request = Request::create('/api/v1/2fa/verify', 'POST', [
            'code' => '123456' // 6-digit TOTP code
        ]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Setup expectations for the Google2FA mock
        $this->google2faMock->shouldReceive('verifyKey')
            ->once()
            ->with('test-secret-key', '123456')
            ->andReturn(true);

        $response = $this->controller->verify($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('2FA verified successfully', $responseData['message']);

        // Verify session was updated
        $this->assertEquals(true, session('2fa_verified'));
    }

    /**
     * Test verification with valid recovery code succeeds.
     */
    public function test_verify_succeeds_with_valid_recovery_code()
    {
        $recoveryCode = 'abcd-1234-efgh-5678';

        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => [
                $recoveryCode,
                'other-recovery-code'
            ]
        ]);

        // Updated to use correct API versioning
        $request = Request::create('/api/v1/2fa/verify', 'POST', [
            'code' => $recoveryCode
        ]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = $this->controller->verify($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Recovery code accepted', $responseData['message']);
        $this->assertEquals(1, $responseData['remaining_codes']);

        // Verify recovery code was removed from user
        $user = $user->fresh();
        $this->assertCount(1, $user->two_factor_recovery_codes);
        $this->assertNotContains($recoveryCode, $user->two_factor_recovery_codes);

        // Verify session was updated
        $this->assertEquals(true, session('2fa_verified'));
    }

    /**
     * Test verification with invalid code fails.
     */
    public function test_verify_fails_with_invalid_code()
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'test-secret-key',
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => ['valid-code-1', 'valid-code-2']
        ]);

        // Updated to use correct API versioning
        $request = Request::create('/api/v1/2fa/verify', 'POST', [
            'code' => '123456'
        ]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Setup expectations for the Google2FA mock
        $this->google2faMock->shouldReceive('verifyKey')
            ->once()
            ->with('test-secret-key', '123456')
            ->andReturn(false);

        $response = $this->controller->verify($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Invalid verification code', $responseData['message']);

        // Verify session was not updated
        $this->assertNull(session('2fa_verified'));
    }
}
