<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::where('role',1)->delete();
        User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => 'hackpeople12',
            'role' => 1,
            'image' => ''
         ]);
    }
}
