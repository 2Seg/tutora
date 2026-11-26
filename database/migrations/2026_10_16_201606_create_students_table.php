<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users', 'id', 'fk_students_user_id_users_id');

            $table->string('email')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');

            $table->string('avatar_img')->nullable();
            $table->string('timezone', 64)->default('UTC');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
