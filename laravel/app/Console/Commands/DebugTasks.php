<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class DebugTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug task reminders.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $this->info('Current time: ' . $now);
        Log::info('Current time: ' . $now);

        $tasks = Task::where('is_completed', false)->get();

        foreach ($tasks as $task) {
            $reminderTime = $task->reminder_time;
            $isDue = $reminderTime->isPast();

            $this->info('Task ' . $task->id . ' | Reminder time: ' . $reminderTime . ' | Is due: ' . ($isDue ? 'Yes' : 'No'));
            Log::info('Task ' . $task->id . ' | Reminder time: ' . $reminderTime . ' | Is due: ' . ($isDue ? 'Yes' : 'No'));
        }
    }
}