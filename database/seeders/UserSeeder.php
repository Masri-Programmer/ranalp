<?php

namespace Database\Seeders;

// Important: Make sure your App\Models\Role extends Spatie\Permission\Models\Role
// and your App\Models\User uses Spatie\Permission\Traits\HasRoles
use App\Models\Role;
use App\Models\User;
use Database\Factories\AddressFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use Kdabrow\SeederOnce\SeederOnce;
class UserSeeder extends SeederOnce
{
    /**:
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstWhere('name', 'admin');
        $developerRole = Role::firstWhere('name', 'developer');
        $userRole = Role::firstWhere('name', 'user');

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

       $testUser = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if ($admin->addresses()->count() === 0) {
            $admin->addresses()->create(
                AddressFactory::new()->make()->toArray()
            );
        }
        $testUser->addresses()->create(
            AddressFactory::new()->make()->toArray()
        );

        if ($adminRole) {
            $roles = array_filter([$adminRole->name, $developerRole?->name]);
            $admin->syncRoles($roles);
        }
        
        $testUser->assignRole($userRole);
        User::factory(20)->create()->each(function ($user) use ($userRole) {

            $user->addresses()->create(
                AddressFactory::new()->make()->toArray()
            );

            if ($userRole && !$user->hasRole($userRole)) {
                $user->assignRole($userRole);
            }
        });
    }
}
