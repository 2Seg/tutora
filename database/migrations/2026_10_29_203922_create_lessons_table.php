<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->foreignId('event_id')
                ->primary()
                ->constrained('events', 'id', 'fk_events_event_id_lessons_id')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->nullable()
                ->constrained('students', 'id', 'fk_events_student_id_students_id')
                ->nullOnDelete();

            $table->string('status');
            $table->string('title');
            $table->string('meeting_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
