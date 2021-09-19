<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            Role::COL_ID => 1,
            Role::COL_NAME => 'CUSTOMER'
        ]);
        Role::create([
            Role::COL_ID => 2,
            Role::COL_NAME => 'MANAGER'
        ]);
        Role::create([
            Role::COL_ID => 3,
            Role::COL_NAME => 'ADMIN'
        ]);
        \App\Models\User::factory(1)->create();
    }
}
