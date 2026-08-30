<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeedrs extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('login')->insert([
            'name'      => 'Super Admin',
            'username'  => 'superadmin',
            'password'  => Hash::make('superadmin'),
            'role'      => 'Super Admin'
        ]);
    }
}
