<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Student;

use App\Data\Student\CreateStudentData;
use App\Enums\LessonStatus;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\User;
use App\Repositories\Student\EloquentStudentRepository;
use Carbon\CarbonInterface;
use Tests\Feature\FeatureTestCase;

final class EloquentStudentRepositoryTest extends FeatureTestCase
{
    private readonly EloquentStudentRepository $studentRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentRepository = new EloquentStudentRepository();
    }

    public function testItRetrievesStudentsFromUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        // Act
        $actual = $this->studentRepository->getAllFromUser($user);

        // Assert
        $this->assertTrue($actual->contains($student));
    }

    public function testItRetrievesStudentsOrderedByUpdatedAtDesc(): void
    {
        // Arrange
        $user = User::factory()->create();
        $oldest = Student::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDays(2)]);
        $newest = Student::factory()->create(['user_id' => $user->id, 'updated_at' => now()]);
        $middle = Student::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDay()]);

        // Act
        $actual = $this->studentRepository->getAllFromUser($user);

        // Assert
        $this->assertSame([$newest->id, $middle->id, $oldest->id], $actual->pluck('id')->all());
    }

    public function testItDoesNotRetrieveStudentsFromAnotherUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user2->id]);

        // Act
        $actual = $this->studentRepository->getAllFromUser($user);

        // Assert
        $this->assertFalse($actual->contains($student));
    }

    public function testItReturnsFalseIfStudentDoesNotBelongToUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $anotherUser->id]);


        // Act
        $actual = $this->studentRepository->belongsToUser($student->id, $user);

        // Assert
        $this->assertFalse($actual);
    }

    public function testItReturnsTrueIfStudentBelongsToUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);


        // Act
        $actual = $this->studentRepository->belongsToUser($student->id, $user);

        // Assert
        $this->assertTrue($actual);
    }

    public function testItStoresStudentForUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $createStudentData = new CreateStudentData('john@doe.com', 'John', 'Doe', '+33612345678', 'Europe/Paris');

        // Act
        $this->studentRepository->storeForUser($user, $createStudentData);

        // Assert
        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'email' => 'john@doe.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+33612345678',
            'timezone' => 'Europe/Paris',
        ]);
    }

    public function testItLoadsTheEarliestUpcomingLessonAsNextLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $this->createLesson($user, $student, now()->addDays(3), LessonStatus::Planned);
        $expected = $this->createLesson($user, $student, now()->addDay(), LessonStatus::Requested);

        // Act
        $actual = $this->studentRepository->getAllFromUser($user);

        // Assert
        $this->assertSame($expected->id, $actual->first()->nextLesson->id);
    }

    public function testItIgnoresPastLessonsForNextLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $this->createLesson($user, $student, now()->subDay(), LessonStatus::Planned);

        // Act
        $actual = $this->studentRepository->getAllFromUser($user);

        // Assert
        $this->assertNull($actual->first()->nextLesson);
    }

    public function testItIgnoresCancelledLessonsForNextLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $this->createLesson($user, $student, now()->addDay(), LessonStatus::Cancelled);
        $expected = $this->createLesson($user, $student, now()->addDays(2), LessonStatus::Planned);

        // Act
        $actual = $this->studentRepository->getAllFromUser($user);

        // Assert
        $this->assertSame($expected->id, $actual->first()->nextLesson->id);
    }

    public function testItIgnoresAnotherStudentLessonsForNextLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id, 'updated_at' => now()]);
        $anotherStudent = Student::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDay()]);
        $this->createLesson($user, $anotherStudent, now()->addDay(), LessonStatus::Planned);

        // Act
        $actual = $this->studentRepository->getAllFromUser($user);

        // Assert
        $this->assertNull($actual->firstWhere('id', $student->id)->nextLesson);
    }

    private function createLesson(User $user, Student $student, CarbonInterface $startsAt, LessonStatus $status): Event
    {
        $event = Event::factory()->withoutAfterCreating()->create([
            'user_id' => $user->id,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHour(),
        ]);
        Lesson::factory()->create(['event_id' => $event->id, 'student_id' => $student->id, 'status' => $status]);

        return $event;
    }
}
