<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'description' => 'System administrator with full access'
            ],
            [
                'name' => 'Organizer',
                'description' => 'Event organizer who can create and manage events'
            ],
            [
                'name' => 'User',
                'description' => 'Regular user who can book events'
            ]
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}