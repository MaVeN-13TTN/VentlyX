<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Event;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'phone_number' => '1234567890'
        ];

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'phone_number' => $userData['phone_number']
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'phone_number' => $userData['phone_number']
        ]);

        $this->assertInstanceOf(User::class, $user);
    }

    public function test_user_has_roles_relationship()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'User']);

        $user->roles()->attach($role);

        $this->assertTrue($user->roles->contains($role));
        $this->assertInstanceOf(Role::class, $user->roles->first());
    }

    public function test_user_has_bookings_relationship()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id
        ]);

        $this->assertTrue($user->bookings->contains($booking));
        $this->assertInstanceOf(Booking::class, $user->bookings->first());
    }

    public function test_user_has_organized_events_relationship()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'organizer_id' => $user->id
        ]);

        $this->assertTrue($user->organizedEvents->contains($event));
        $this->assertInstanceOf(Event::class, $user->organizedEvents->first());
    }

    public function test_user_can_check_role()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'Admin']);

        $user->roles()->attach($role);

        $this->assertTrue($user->hasRole('Admin'));
        $this->assertFalse($user->hasRole('User'));
    }
}
