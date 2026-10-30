<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => config('dev.user.email')],
            User::factory()->raw([
                'email' => config('dev.user.email'),
                'first_name' => config('dev.user.first_name'),
                'last_name' => config('dev.user.last_name'),
                'password' => Hash::make(config('dev.user.password')),
            ]),
        );
    }
}
