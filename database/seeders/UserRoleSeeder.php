<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_roles')->insertOrIgnore([
            ['role_id' => 1, 'role_title' => 'Exec'],
            ['role_id' => 2, 'role_title' => 'Admin'],
            ['role_id' => 3, 'role_title' => 'Instructor'],
            ['role_id' => 4, 'role_title' => 'Student'],
        ]);
    }
}
