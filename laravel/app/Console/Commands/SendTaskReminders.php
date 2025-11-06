<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Mail\TaskReminderMail;
use Illuminate\Support\Facades\Mail;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-task-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for tasks that are due.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tasks = Task::where('reminder_time', '<= ', now())
                       ->where('is_completed', false)
                       ->get();

        foreach ($tasks as $task) {
            Mail::to($task->user->email)->send(new TaskReminderMail($task));
            $task->update(['is_completed' => true]);
        }

        $this->info('Task reminders sent successfully!');
    }
}