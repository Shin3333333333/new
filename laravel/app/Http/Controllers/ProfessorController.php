<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;

class ProfessorController extends Controller
{
    public function updateDetails(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string',
            'specialization' => 'required|string',
            'type' => 'required|string',
            'time_unavailable' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $user->update(['name' => $request->name]);

        $user->professor()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $request->name,
                'department' => $request->specialization, // Corrected from specialization to department
                'type' => $request->type,
                'time_unavailable' => $request->time_unavailable,
                'status' => $request->status,
            ]
        );

        if ($user->is_temporary) {
            $user->update(['is_temporary' => 0]);
        }

        return response()->json(['message' => 'Professor details updated successfully.']);
    }

    public function getDetails(Request $request)
    {
        $user = Auth::user()->load('professor');

        if ($user->professor) {
            return response()->json([
                'name' => $user->name,
                'department' => $user->professor->department,
                'type' => $user->professor->type,
                'time_unavailable' => $user->professor->time_unavailable,
                'status' => $user->professor->status ?? 'Pending',
            ]);
        } else {
            return response()->json([
                'name' => $user->name,
                'department' => '',
                'type' => '',
                'time_unavailable' => '',
                'status' => 'Pending',
            ]);
        }
    }

    public function getSchedule(Request $request)
    {
        try {
            $user = Auth::user();
            $professor = $user->professor;

            if (!$professor) {
                Log::warning('No professor record found for user_id: ' . $user->id);
                return response()->json([
                    'schedule' => [],
                    'totalLoad' => 0,
                    'subjects' => [],
                ]);
            }

            $activeSchedule = \App\Models\ActiveSchedule::latest()->first();

            if (!$activeSchedule) {
                Log::warning('No active schedule found.');
                return response()->json([
                    'schedule' => [],
                    'totalLoad' => 0,
                    'subjects' => [],
                ]);
            }

            $batch_id = $activeSchedule->batch_id;
            $faculty_id = $professor->id;

            Log::info("Active schedule batch_id: {$batch_id}, faculty_id: {$faculty_id}");

            $schedule = \App\Models\FinalizedSchedule::with('room')->where('batch_id', $batch_id)
                ->where('faculty_id', $faculty_id)
                ->get();

            $schedule->transform(function ($item) {
                $timeParts = explode(' ', $item->time);
                $item->day = $timeParts[0] ?? null;
                if (isset($timeParts[1])) {
                    $timeRangeParts = explode('-', $timeParts[1]);
                    $item->time_start = $timeRangeParts[0] ?? null;
                    $item->time_end = $timeRangeParts[1] ?? null;
                } else {
                    $item->time_start = null;
                    $item->time_end = null;
                }
                return $item;
            });

            Log::info("Found {$schedule->count()} schedule entries.");

            $totalLoad = $schedule->sum('units');
            $subjects = $schedule->pluck('subject')->unique()->values();

            return response()->json([
                'schedule' => $schedule,
                'totalLoad' => $totalLoad,
                'subjects' => $subjects,
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()], 500);
        }
    }
}