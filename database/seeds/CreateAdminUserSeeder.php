<?php

use Illuminate\Database\Seeder;
use App\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
        	'name' => 'Administrator', 
            'email' => 'admin@webportal.ac',
        	'password' => bcrypt('123$qweR'),
            'branch_id' => 1,
            'position_id' => 1,
            'active' => 'Y',
        ]);

        $role = Role::create(['name' => 'Administrator']);

        $permissions = Permission::pluck('id','id')->all();

        $role->syncPermissions($permissions);

        $user->assignRole([$role->id]);
    }
}
