<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that 2FA is not required by default for new users.
     */
    public function test_two_factor_not_required_by_default()
    {
        $user = User::factory()->create();

        // Using assertNotTrue to handle both false and null values
        $this->assertNotTrue($user->two_factor_enabled);
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertFalse($user->requiresTwoFactor());
    }

    /**
     * Test that a user with 2FA enabled but not confirmed doesn't require 2FA.
     */
    public function test_unconfirmed_two_factor_doesnt_require_verification()
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'test-secret',
            'two_factor_recovery_codes' => ['code1', 'code2'],
            'two_factor_confirmed_at' => null
        ]);

        $this->assertTrue($user->two_factor_enabled);
        $this->assertFalse($user->requiresTwoFactor());
    }

    /**
     * Test that a user with 2FA enabled and confirmed requires 2FA.
     */
    public function test_confirmed_two_factor_requires_verification()
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'test-secret',
            'two_factor_recovery_codes' => ['code1', 'code2'],
            'two_factor_confirmed_at' => now()
        ]);

        $this->assertTrue($user->two_factor_enabled);
        $this->assertTrue($user->requiresTwoFactor());
    }

    /**
     * Test that the 2FA fields can be updated properly.
     */
    public function test_two_factor_fields_can_be_updated()
    {
        $user = User::factory()->create();

        $recoveryCodes = ['code1', 'code2', 'code3'];
        $now = now();

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'new-secret',
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => $now
        ]);

        $user = $user->fresh();

        $this->assertTrue($user->two_factor_enabled);
        $this->assertEquals('new-secret', $user->two_factor_secret);
        $this->assertEquals($recoveryCodes, $user->two_factor_recovery_codes);
        $this->assertEquals($now->toDateTimeString(), $user->two_factor_confirmed_at->toDateTimeString());
    }

    /**
     * Test that 2FA fields are properly casted.
     */
    public function test_two_factor_fields_are_properly_casted()
    {
        $recoveryCodes = ['code1', 'code2', 'code3'];
        $time = now();

        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => $time
        ]);

        $this->assertIsBool($user->two_factor_enabled);
        $this->assertIsArray($user->two_factor_recovery_codes);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->two_factor_confirmed_at);
    }

    /**
     * Test that sensitive 2FA data is hidden from serialization.
     */
    public function test_sensitive_two_factor_data_is_hidden()
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'secret-key',
            'two_factor_recovery_codes' => ['code1', 'code2']
        ]);

        $serialized = $user->toArray();

        $this->assertArrayHasKey('two_factor_enabled', $serialized);
        $this->assertArrayNotHasKey('two_factor_secret', $serialized);
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $serialized);
    }

    /**
     * Test that 2FA can be disabled by clearing all related fields.
     */
    public function test_two_factor_can_be_disabled()
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'test-secret',
            'two_factor_recovery_codes' => ['code1', 'code2'],
            'two_factor_confirmed_at' => now()
        ]);

        $this->assertTrue($user->requiresTwoFactor());

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null
        ]);

        $user = $user->fresh();

        $this->assertFalse($user->two_factor_enabled);
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertFalse($user->requiresTwoFactor());
    }
}
