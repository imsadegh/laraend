<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseWatchTime;
use App\Models\CourseModule;
use App\Models\User;

class CourseWatchTimeSeeder extends Seeder
{
    public function run(): void
    {
        // Get test data
        $modules = CourseModule::where('type', 'video')->get();
        $students = User::where('role_id', 3)->get(); // Students only

        if ($modules->isEmpty() || $students->isEmpty()) {
            return; // Skip if no video modules or students
        }

        foreach ($modules as $module) {
            foreach ($students as $student) {
                // Vary watch progress: 0%, 25%, 50%, 75%, 100%
                $watchPercentages = [0, 0.25, 0.5, 0.75, 1.0];
                $percentage = $watchPercentages[rand(0, 4)];
                
                $videoDuration = $module->estimated_duration_seconds ?? 600;
                $watchedSeconds = (int)($videoDuration * $percentage);

                CourseWatchTime::create([
                    'course_module_id' => $module->id,
                    'user_id' => $student->id,
                    'watch_time_seconds' => $watchedSeconds,
                    'last_position' => $watchedSeconds,
                    'session_id' => \Illuminate\Support\Str::uuid(),
                    'started_at' => now()->subDays(rand(0, 30)),
                    'last_accessed_at' => now()->subDays(rand(0, 5)),
                    'events' => json_encode($this->generateWatchEvents($watchedSeconds, $videoDuration)),
                ]);
            }
        }
    }

    /**
     * Generate realistic watch events for testing
     */
    private function generateWatchEvents(int $watchedSeconds, int $totalDuration): array
    {
        $events = [];
        $currentTime = 0;
        $timestamp = now()->getTimestamp();

        // Initial play event
        $events[] = [
            'type' => 'play',
            'position' => 0,
            'timestamp' => $timestamp,
        ];

        // Simulate watch pattern with pauses
        while ($currentTime < $watchedSeconds) {
            // Random watch interval (30 seconds to 5 minutes)
            $watchInterval = rand(30, 300);
            $currentTime = min($currentTime + $watchInterval, $watchedSeconds);
            $timestamp += $watchInterval;

            // Add pause event
            $events[] = [
                'type' => 'pause',
                'position' => $currentTime,
                'timestamp' => $timestamp,
            ];

            // 30% chance of resume
            if (rand(1, 100) <= 30 && $currentTime < $watchedSeconds) {
                $timestamp += rand(60, 600); // Gap between sessions
                $events[] = [
                    'type' => 'play',
                    'position' => $currentTime,
                    'timestamp' => $timestamp,
                ];
            }
        }

        // Add finish event if fully watched
        if ($watchedSeconds >= $totalDuration * 0.95) {
            $events[] = [
                'type' => 'finish',
                'position' => $watchedSeconds,
                'timestamp' => $timestamp + 10,
            ];
        }

        return $events;
    }
}
