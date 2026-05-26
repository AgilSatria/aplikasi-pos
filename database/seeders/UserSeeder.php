<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'super-admin@pos.com',
            'password' => Hash::make('password'),
        ])->assignRole('super-admin');

        User::create([
            'name' => 'Cashier',
            'email' => 'cashier@pos.com',
            'password' => Hash::make('password'),
        ])->assignRole('cashier');
    }
}
