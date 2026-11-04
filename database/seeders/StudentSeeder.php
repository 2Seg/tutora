<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class StudentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Student::factory()
            ->count(20)
            ->create();
    }
}
