<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::create([
        //     'name' => 'Administrator',
        //     'password' =>bcrypt('admin')
        // ]);

        $role = Role::findByName('admin', 'api');
        $permissions = Permission::all();

        $role->syncPermissions($permissions);

        $user = User::find(1);
        $user->assignRole($role->name);
    }
}
