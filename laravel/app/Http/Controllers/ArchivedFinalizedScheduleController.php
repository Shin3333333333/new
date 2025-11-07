<?php

namespace App\Http\Controllers;

use App\Models\ArchivedFinalizedSchedule;
use Illuminate\Http\Request;

class ArchivedFinalizedScheduleController extends Controller
{
    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $batch_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($academicYear, $semester, $batch_id)
    {
        try {
            ArchivedFinalizedSchedule::where('academicYear', $academicYear)
                ->where('semester', $semester)
                ->where('batch_id', $batch_id)
                ->delete();

            return response()->json(['success' => true, 'message' => 'Archive deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete archive.'], 500);
        }
    }
}