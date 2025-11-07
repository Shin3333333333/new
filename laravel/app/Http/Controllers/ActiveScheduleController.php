<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActiveSchedule;
use Illuminate\Support\Carbon;
use App\Models\FinalizedSchedule;
use App\Models\ArchivedFinalizedSchedule;
use Illuminate\Support\Facades\DB;

class ActiveScheduleController extends Controller
{
    // Get currently active schedule (normalized response)
    public function getActive()
    {
        $active = ActiveSchedule::latest()->first();
        if (!$active) {
            return response()->json([]);
        }

        $date = $active->staged_at ?? $active->created_at;

        return response()->json([
            'academicYear' => $active->academicYear,
            'semester' => $active->semester,
            'batch_id' => $active->batch_id,
            'staged_at' => $date ? Carbon::parse($date)->toDateTimeString() : null,
        ]);
    }

    // Set a schedule as active (accepts batch_id)
    public function setActive(Request $request)
    {
        $validated = $request->validate([
            'academicYear' => 'required|string',
            'semester' => 'required|string',
            'batch_id' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            // Find the current active schedule
            $currentActive = ActiveSchedule::latest()->first();
            $newBatchId = $validated['batch_id'] ?? null;

            // Only archive if there is a current active schedule and its batch_id is different from the new one.
            if ($currentActive && $currentActive->batch_id !== $newBatchId) {
                // Find the corresponding finalized schedules to archive
                $schedulesToArchive = FinalizedSchedule::where('batch_id', $currentActive->batch_id)->get();

                if ($schedulesToArchive->isNotEmpty()) {
                    $archiveData = $schedulesToArchive->map(function ($schedule) {
                        return [
                            'user_id' => $schedule->user_id,
                            'batch_id' => $schedule->batch_id,
                            'faculty_id' => $schedule->faculty_id,
                            'faculty' => $schedule->faculty,
                            'subject' => $schedule->subject,
                            'time' => $schedule->time,
                            'classroom' => $schedule->classroom,
                            'course_code' => $schedule->course_code,
                            'course_section' => $schedule->course_section,
                            'units' => $schedule->units,
                            'academicYear' => $schedule->academicYear,
                            'semester' => $schedule->semester,
                            'status' => 'archived',
                            'payload' => json_encode($schedule->payload),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    })->toArray();

                    // Bulk insert into archives
                    ArchivedFinalizedSchedule::insert($archiveData);

                    // Delete the archived schedules from finalized_schedules
                    FinalizedSchedule::where('batch_id', $currentActive->batch_id)->delete();
                }
            }

            // Clear any previous active schedules
            ActiveSchedule::query()->delete();

            // Save new active one
            $active = new ActiveSchedule();
            $active->academicYear = $validated['academicYear'];
            $active->semester = $validated['semester'];
            if ($newBatchId) {
                $active->batch_id = $newBatchId;
            }
            $active->staged_at = now();
            $active->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Active schedule updated successfully and previous one archived.',
        ]);
    }
}