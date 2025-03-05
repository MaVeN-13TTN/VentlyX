<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a user with the given role
     */
    protected function createUserWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    /**
     * Create a standard user
     */
    protected function createUser(): User
    {
        return User::factory()->create();
    }

    /**
     * Create an admin user
     */
    protected function createAdmin(): User
    {
        return $this->createUserWithRole('Admin');
    }

    /**
     * Create an organizer user
     */
    protected function createOrganizer(): User
    {
        return $this->createUserWithRole('Organizer');
    }

    /**
     * Acting as a user with authentication
     */
    protected function actingAsUser(?User $user = null): self
    {
        $user = $user ?? $this->createUser();
        return $this->actingAs($user);
    }

    /**
     * Acting as an admin user
     */
    protected function actingAsAdmin(): self
    {
        return $this->actingAs($this->createAdmin());
    }

    /**
     * Acting as an organizer
     */
    protected function actingAsOrganizer(): self
    {
        return $this->actingAs($this->createOrganizer());
    }
}
