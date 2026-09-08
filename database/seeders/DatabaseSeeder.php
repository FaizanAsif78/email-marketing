<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Rehman',
            'email' => 'rehman@gmail.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Abdullah',
            'email' => 'abdullah@gmail.com',
            'password' => Hash::make('password'),
        ]);
    }
}
