<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'permission-list',
            'permission-create',
            'permission-edit',
            'permission-delete',
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'branch-list',
            'branch-create',
            'branch-edit',
            'branch-delete',    
            'company-list',
            'company-create',
            'company-edit',
            'company-delete',
            'position-list',
            'position-create',
            'position-edit',
            'position-delete',
            'department-list',
            'department-create',
            'department-edit',
            'department-delete',
            'division-list',
            'division-create',
            'division-edit',
            'division-delete',
            'file-explorer',
            'file-list',
            'file-create',
            'file-edit',
            'file-delete',
            'activity-logs',
            'qr-token-create',
            'qr-token-delete',
            
         ];
 
 
         foreach ($permissions as $permission) {
              Permission::firstOrCreate(['name' => $permission]);
         }
    }
}
