<?php

namespace App\Http\Controllers;

use App\Models\StudentExcuseLetter;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class StudentExcuseLetterController extends Controller
{
    public function index($user_id)
    {
        // Authorize: students can view their own, admins can view any
        if (Auth::user()->role_id == 4 && Auth::id() != $user_id) {
            abort(403);
        }
        return view('student.excuse-letters.index', compact('user_id'));
    }

    public function create($user_id)
    {
        // Only students can create for themselves, or admins
        if (Auth::user()->role_id == 4 && Auth::id() != $user_id) {
            abort(403);
        }
        $date = request('date');
        $session = request('session');
        $attendance_id = request('attendance_id');
        return view('student.excuse-letters.create', compact('user_id', 'date', 'session', 'attendance_id'));
    }

    public function store(Request $request, $user_id)
    {
        $this->authorize('create', StudentExcuseLetter::class);

        $data = $request->validate([
            'date_attendance' => 'required|date',
            'reason' => 'required|string|max:1000',
            'status' => 'nullable|string|max:50',
            'letter_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('letter_file')) {
            $path = $request->file('letter_file')->store('letters', 'public');
            $data['letter_link'] = $path;
        }

        $data['user_id'] = $user_id;
        $data['status'] = $data['status'] ?? 'pending';

        $letter = StudentExcuseLetter::create($data);

        // Link any existing attendance rows for this user/date
        Attendance::where('user_id', $letter->user_id)
            ->whereDate('date', $letter->date_attendance)
            ->update(['letter_id' => $letter->letter_id]);

        return redirect()->route('student.excuse-letters.index', $user_id)->with('success', 'Excuse letter created successfully.');
    }

    public function show($user_id, $letter_id)
    {
        $letter = StudentExcuseLetter::where('user_id', $user_id)->findOrFail($letter_id);
        $this->authorize('view', $letter);
        return view('student.excuse-letters.show', compact('user_id', 'letter_id'));
    }

    public function edit($user_id, $letter_id)
    {
        $letter = StudentExcuseLetter::where('user_id', $user_id)->findOrFail($letter_id);
        $this->authorize('update', $letter);
        return view('student.excuse-letters.edit', compact('user_id', 'letter_id'));
    }

    public function update(Request $request, $user_id, $letter_id)
    {
        $letter = StudentExcuseLetter::where('user_id', $user_id)->findOrFail($letter_id);
        $this->authorize('update', $letter);

        $data = $request->validate([
            'date_attendance' => 'required|date',
            'reason' => 'required|string|max:1000',
            'status' => 'nullable|string|max:50',
            'letter_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('letter_file')) {
            // Delete old file if exists
            if ($letter->letter_link) {
                if (str_starts_with($letter->letter_link, '/storage/')) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $letter->letter_link));
                } else {
                    Storage::disk('public')->delete($letter->letter_link);
                }
            }
            $path = $request->file('letter_file')->store('letters', 'public');
            $data['letter_link'] = $path;
        }

        // For students, don't update status
        if (Auth::user()->role_id == 4) {
            unset($data['status']);
        }

        $letter->update($data);

        // Re-link attendance rows for this user/date
        Attendance::where('user_id', $letter->user_id)
            ->whereDate('date', $letter->date_attendance)
            ->update(['letter_id' => $letter->letter_id]);

        return redirect()->route('student.excuse-letters.index', $user_id)->with('success', 'Excuse letter updated successfully.');
    }

    public function destroy($user_id, $letter_id)
    {
        $letter = StudentExcuseLetter::where('user_id', $user_id)->findOrFail($letter_id);
        $this->authorize('delete', $letter);

        // Delete file if exists
        if ($letter->letter_link) {
            if (str_starts_with($letter->letter_link, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $letter->letter_link));
            } else {
                Storage::disk('public')->delete($letter->letter_link);
            }
        }

        $letter->delete();

        return redirect()->route('student.excuse-letters.index', $user_id)->with('success', 'Excuse letter deleted successfully.');
    }

    public function download($id)
    {
        $letter = StudentExcuseLetter::findOrFail($id);
        $this->authorize('view', $letter);

        if (!$letter->letter_link) {
            abort(404);
        }

        if (str_starts_with($letter->letter_link, '/storage/')) {
            $path = public_path(str_replace('/storage/', 'storage/', $letter->letter_link));
        } else {
            $path = storage_path('app/public/' . $letter->letter_link);
        }

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }
}
