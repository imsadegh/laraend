<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\Question;
use App\Models\ExamQuestion;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        // Create exams for courses using actual table schema
        $exam1 = Exam::create([
            'course_id' => 1,
            'created_by' => 1,
            'name' => 'آزمون آنلاین Vue.js',
            'intro' => 'تست دانش خود در Vue.js - 10 سوال، 30 دقیقه',
            'time_open' => now(),
            'time_close' => now()->addMonth(),
            'time_limit' => 30,
            'attempts' => 3,
            'feedback_enabled' => true,
            'shuffle_questions' => false,
            'shuffle_answers' => false,
            'questions_count' => 5,
            'is_published' => true,
            'status' => 'active',
            'exam_type' => 'quiz',
        ]);

        $exam2 = Exam::create([
            'course_id' => 2 ?? 1,
            'created_by' => 2 ?? 1,
            'name' => 'آزمون نهایی Laravel',
            'intro' => 'امتحان جامع درس Laravel - 20 سوال، 60 دقیقه',
            'time_open' => now(),
            'time_close' => now()->addMonth(),
            'time_limit' => 60,
            'attempts' => 2,
            'feedback_enabled' => false,
            'shuffle_questions' => true,
            'shuffle_answers' => true,
            'questions_count' => 4,
            'is_published' => true,
            'status' => 'active',
            'exam_type' => 'exam',
        ]);

        // Create questions for Exam 1
        $questions1 = [
            [
                'type' => 'multiple_choice',
                'question_text' => 'Vue.js در کدام سال منتشر شد؟',
                'options' => json_encode([
                    'الف' => '2013',
                    'ب' => '2014',
                    'ج' => '2015',
                    'د' => '2016',
                ]),
                'correct_answer' => 'ب',
                'points' => 1,
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'مبانی Vue.js کدام است؟',
                'options' => json_encode([
                    'الف' => 'Reactive Data Binding',
                    'ب' => 'Component-Based Architecture',
                    'ج' => 'Both A and B',
                    'د' => 'Neither',
                ]),
                'correct_answer' => 'ج',
                'points' => 1,
            ],
            [
                'type' => 'true_false',
                'question_text' => 'Vue.js میتواند برای توسعه تک صفحه ای استفاده شود.',
                'options' => json_encode(['درست', 'نادرست']),
                'correct_answer' => 'درست',
                'points' => 1,
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'کدام یک از این فریم‌ورک‌های جاوا اسکریپت است؟',
                'options' => json_encode([
                    'الف' => 'React',
                    'ب' => 'Angular',
                    'ج' => 'Vue',
                    'د' => 'همه موارد',
                ]),
                'correct_answer' => 'د',
                'points' => 1,
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'v-model در Vue برای چه استفاده می‌شود؟',
                'options' => json_encode([
                    'الف' => 'Two-way data binding',
                    'ب' => 'Conditional rendering',
                    'ج' => 'List rendering',
                    'د' => 'Event handling',
                ]),
                'correct_answer' => 'الف',
                'points' => 1,
            ],
        ];

        foreach ($questions1 as $index => $questionData) {
            $question = Question::create([
                'created_by' => 1,
                'type' => $questionData['type'],
                'question_text' => $questionData['question_text'],
                'options' => $questionData['options'],
                'correct_answer' => $questionData['correct_answer'],
                'points' => $questionData['points'],
                'difficulty' => ['easy', 'medium', 'hard'][$index % 3],
                'explanation' => 'توضیحات برای پاسخ صحیح...',
            ]);

            ExamQuestion::create([
                'exam_id' => $exam1->id,
                'question_id' => $question->id,
                'order' => $index + 1,
            ]);
        }

        // Create questions for Exam 2
        $questions2 = [
            [
                'type' => 'multiple_choice',
                'question_text' => 'Laravel توسط کی ایجاد شد؟',
                'options' => json_encode([
                    'الف' => 'Taylor Otwell',
                    'ب' => 'Evan You',
                    'ج' => 'Dan Abramov',
                    'د' => 'Rasmus Lerdorf',
                ]),
                'correct_answer' => 'الف',
                'points' => 1,
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'کدام نسخه Laravel آخرین نسخه LTS است؟',
                'options' => json_encode([
                    'الف' => '8.x',
                    'ب' => '9.x',
                    'ج' => '10.x',
                    'د' => '11.x',
                ]),
                'correct_answer' => 'د',
                'points' => 1,
            ],
            [
                'type' => 'true_false',
                'question_text' => 'Laravel یک framework PHP است.',
                'options' => json_encode(['درست', 'نادرست']),
                'correct_answer' => 'درست',
                'points' => 1,
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'ORM پیشفرض Laravel کدام است؟',
                'options' => json_encode([
                    'الف' => 'Doctrine',
                    'ب' => 'Propel',
                    'ج' => 'Eloquent',
                    'د' => 'Outburst',
                ]),
                'correct_answer' => 'ج',
                'points' => 1,
            ],
        ];

        foreach ($questions2 as $index => $questionData) {
            $question = Question::create([
                'created_by' => 2 ?? 1,
                'type' => $questionData['type'],
                'question_text' => $questionData['question_text'],
                'options' => $questionData['options'],
                'correct_answer' => $questionData['correct_answer'],
                'points' => $questionData['points'],
                'difficulty' => ['easy', 'medium'][$index % 2],
                'explanation' => 'توضیحات جامع برای این سوال...',
            ]);

            ExamQuestion::create([
                'exam_id' => $exam2->id,
                'question_id' => $question->id,
                'order' => $index + 1,
            ]);
        }
    }
}
