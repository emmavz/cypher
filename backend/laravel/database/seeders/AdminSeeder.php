<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('admins')->delete();

        $admin = new Admin();
        $admin->name = "admin";
        $admin->email = "admin@site.com";
        $admin->password = bcrypt('admin123');
        $admin->save();

        $roleName = 'Super Admin';

        \DB::table('roles')->delete();

        $role = new Role();
        $role->name = $roleName;
        $role->guard_name = 'admin';
        $role->save();

        $admin->assignRole($roleName);
    }
}
