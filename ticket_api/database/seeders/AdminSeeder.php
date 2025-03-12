<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@ferryticket.com',
            'password' => Hash::make('adminpassword'),
            'role' => 'SUPER_ADMIN',
        ]);

        // Create admin
        Admin::create([
            'name' => 'System Admin',
            'email' => 'system@ferryticket.com',
            'password' => Hash::make('systempassword'),
            'role' => 'ADMIN',
        ]);

        // Create operator
        Admin::create([
            'name' => 'Ferry Operator',
            'email' => 'operator@ferryticket.com',
            'password' => Hash::make('operatorpassword'),
            'role' => 'OPERATOR',
        ]);
    }
}
