<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index()
    {
        return Task::where('user_id', Auth::id())->get();
    }

    public function store(Request $request)
    {
        Log::info('Task creation request:', $request->all());

        $request->validate([
            'text' => 'required|string|max:255',
            'reminder_time' => 'nullable|date',
        ]);

        $task = new Task();
        $task->text = $request->text;
        $task->reminder_time = $request->reminder_time;
        $task->user_id = Auth::id();

        Log::info('Task model before save:', ['reminder_time' => $task->reminder_time]);

        $task->save();

        // Eager load the user relationship before returning the response
        $task->load('user');

        return response()->json($task, 201);
    }

    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task->delete();

        return response()->json(null, 204);
    }
}