<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Tambahkan ini di atas

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat akun admin
        User::create([
            'name' => 'Admin Shellemerch',
            'email' => 'shellemerch@gmail.com', // Ini email untuk login
            'password' => Hash::make('tutupbotolsulik'), // Ini passwordnya
        ]);
    }
}