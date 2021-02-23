<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Igor',
            'email' => 'ivmello@gmail.com',
            'password' => bcrypt(10203040),
        ]);

        User::create([
            'name' => 'Rico',
            'email' => 'rico@gmail.com',
            'password' => bcrypt(10203040),
        ]);

        User::create([
            'name' => 'Karenini',
            'email' => 'karenini@gmail.com',
            'password' => bcrypt(10203040),
        ]);

        User::create([
            'name' => 'Jess',
            'email' => 'jess@gmail.com',
            'password' => bcrypt(10203040),
        ]);
    }
}
