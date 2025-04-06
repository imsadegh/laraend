<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = \App\Models\Course::class;

    public function definition()
    {

        return [
            'course_name' => $this->faker->sentence(3), // Generate a course name
            'course_code' => $this->faker->unique()->regexify('[A-Z]{4}-[0-9]{6}'),

            'instructor_id' => $this->faker->randomElement([2, 4]),
            'assistant_id' => $this->faker->numberBetween(2, 7),
            'category_id' => $this->faker->numberBetween(1, 7),

            'tuition_fee' => $this->faker->randomFloat(0, 5000000, 50000000),
            'capacity' => $this->faker->numberBetween(20, 100),
            'visibility' => $this->faker->boolean(),
            'featured' => $this->faker->boolean(),

            'description' => $this->faker->paragraph(),
            'about' => $this->faker->paragraph(),

            'discussion_group_url' => $this->faker->url(),
            'skill_level' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            // 'is_free' => $this->faker->boolean(),
            'total_lectures' => $this->faker->numberBetween(5, 50),
            'lecture_length' => $this->faker->numberBetween(10, 60),
            'total_quizzes' => $this->faker->numberBetween(0, 10),
            'total_assignments' => $this->faker->numberBetween(0, 10),
            'total_resources' => $this->faker->numberBetween(0, 10),
            'language' => $this->faker->randomElement(['en', 'fr', 'ar', 'fa']),
            'is_captions' => $this->faker->boolean(),
            'is_certificate' => $this->faker->boolean(),
            'is_quiz' => $this->faker->boolean(),
            'is_assignment' => $this->faker->boolean(),
            // 'table_of_content' => json_encode($this->faker->words(5)),
            'status' => $this->faker->randomElement(['active', 'archived', 'draft']),
            'is_finished' => $this->faker->boolean(),
            'enrolled_students_count' => $this->faker->numberBetween(0, 100),
            'allow_waitlist' => $this->faker->boolean(),

            'start_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+6 months'),

            'prerequisites' => json_encode($this->faker->words(3)),
            'tags' => json_encode($this->faker->words(5)),
            'thumbnail_url' => $this->faker->imageUrl(),

            'rating' => $this->faker->randomFloat(2, 0, 5),
        ];
    }
}
