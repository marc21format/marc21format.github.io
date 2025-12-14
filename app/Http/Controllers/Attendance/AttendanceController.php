<?php

namespace App\Http\Controllers\Attendance;

use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Controllers\Controller;

class AttendanceController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Attendance::class);
        return response()->json(Attendance::with(['user','letter','recordedBy','updatedBy'])->paginate(30));
    }

    public function show(Attendance $attendance)
    {
        $this->authorize('view', $attendance);
        return response()->json($attendance->load(['user','letter','recordedBy','updatedBy']));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Attendance::class);
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'nullable|date',
            'attendance_time' => 'nullable|date_format:H:i:s',
            'session' => 'nullable|in:auto,am,pm',
        ]);

        $service = app(AttendanceService::class);
        $attendance = $service->createOrUpdateAttendance(
            $data['user_id'],
            $data['date'] ?? null,
            $data['attendance_time'] ?? null,
            $data['session'] ?? 'auto',
            Auth::id()
        );

        try {
            $attendance->attachLetterIfExists();
        } catch (\Throwable $e) {}

        $statusCode = $attendance->wasRecentlyCreated ? 201 : 200;
        return response()->json($attendance->load(['user','letter']), $statusCode);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $actor = Auth::user();
        /** @var User|null $actor */
        $targetUser = User::find($attendance->user_id);
        if (! ($actor instanceof User) || ! $actor->canEditUserProfile($targetUser)) {
            return response()->json(['message'=>'Forbidden'],403);
        }

        $data = $request->validate([
            'date' => 'nullable|date',
            'attendance_time' => 'nullable|date_format:H:i:s',
            'session' => 'nullable|in:am,pm',
        ]);

        $updated = DB::transaction(function () use ($attendance, $data, $actor) {
            if (isset($data['session']) && $data['session'] !== $attendance->session) {
                $conflict = Attendance::where('user_id', $attendance->user_id)
                    ->whereDate('date', $data['date'] ?? $attendance->date)
                    ->where('session', $data['session'])
                    ->lockForUpdate()
                    ->first();

                if ($conflict && $conflict->attendance_id !== $attendance->attendance_id) {
                    throw new \RuntimeException('Another attendance already exists for that session');
                }
            }

            $attendance->fill($data);
            $attendance->updated_by = $actor->id;
            $attendance->save();
            return $attendance;
        });

        $updated->attachLetterIfExists();
        return response()->json($updated->load(['user','letter']));
    }

    public function destroy(Attendance $attendance)
    {
        $actor = Auth::user();
        /** @var User|null $actor */
        if (! ($actor instanceof User) || ! $actor->isStaff()) {
            return response()->json(['message'=>'Forbidden'],403);
        }
        $attendance->delete();
        return response()->json(['message'=>'deleted']);
    }
}
