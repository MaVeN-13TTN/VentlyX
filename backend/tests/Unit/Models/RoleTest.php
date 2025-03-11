<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_can_be_created()
    {
        $roleData = [
            'name' => 'Admin',
            'description' => 'Administrator role with full access'
        ];

        $role = Role::create($roleData);

        $this->assertDatabaseHas('roles', [
            'name' => $roleData['name'],
            'description' => $roleData['description']
        ]);

        $this->assertInstanceOf(Role::class, $role);
    }

    public function test_role_has_users_relationship()
    {
        $role = Role::factory()->create();
        $user = User::factory()->create();

        $role->users()->attach($user);

        $this->assertTrue($role->users->contains($user));
        $this->assertInstanceOf(User::class, $role->users->first());
    }

    public function test_role_can_be_assigned_to_multiple_users()
    {
        $role = Role::factory()->create();
        $users = User::factory()->count(3)->create();

        $role->users()->attach($users);

        $this->assertEquals(3, $role->users()->count());
        $users->each(function ($user) use ($role) {
            $this->assertTrue($role->users->contains($user));
        });
    }

    public function test_role_can_be_removed_from_user()
    {
        $role = Role::factory()->create();
        $user = User::factory()->create();

        $role->users()->attach($user);
        $this->assertTrue($role->users->contains($user));

        $role->users()->detach($user);
        $this->assertFalse($role->fresh()->users->contains($user));
    }

    public function test_role_name_is_unique()
    {
        Role::create(['name' => 'Admin']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Role::create(['name' => 'Admin']);
    }

    public function test_role_can_be_found_by_name()
    {
        $role = Role::factory()->create(['name' => 'Organizer']);

        $foundRole = Role::where('name', 'Organizer')->first();

        $this->assertNotNull($foundRole);
        $this->assertEquals($role->id, $foundRole->id);
    }
}
