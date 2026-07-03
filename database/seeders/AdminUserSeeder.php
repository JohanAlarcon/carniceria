<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'johandarioalarcon@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'is_staff' => true,
                'phone' => null,
                'email_verified_at' => now(),
            ]
        );
    }
}
