<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (app()->isLocal()) {
            $this->call(UserSeeder::class);
        }

        $this->call([
            StudentSeeder::class,
            EventSeeder::class,
        ]);
    }
}
