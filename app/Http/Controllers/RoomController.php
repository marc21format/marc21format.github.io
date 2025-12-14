<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;

class RoomController extends Controller
{
    // Web views for CRUD pages
    public function index()
    {
        $user = auth()->user();
        if (! $user || (! $user->isAdmin() && ! $user->isExecutive())) {
            abort(403);
        }
        return view('rooms.index');
    }

    public function create()
    {
        $user = auth()->user();
        if (! $user || (! $user->isAdmin() && ! $user->isExecutive())) {
            abort(403);
        }
        return view('rooms.create');
    }

    public function edit(Room $room)
    {
        $user = auth()->user();
        if (! $user || (! $user->isAdmin() && ! $user->isExecutive())) {
            abort(403);
        }
        return view('rooms.edit', ['room' => $room]);
    }

    // JSON / API-style endpoints (used by JS/Livewire if needed)
    public function apiIndex()
    {
        $this->authorize('viewAny', Room::class);
        return response()->json(Room::with(['adviser','president','secretary'])->paginate(20));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Room::class);
        $data = $request->validate([
            'group' => 'required|string',
            'adviser_id' => 'nullable|exists:users,id',
            'president_id' => 'nullable|exists:users,id',
            'secretary_id' => 'nullable|exists:users,id',
        ]);
        $r = Room::create($data);
        return response()->json($r, 201);
    }

    public function update(Request $request, Room $room)
    {
        $this->authorize('update', $room);
        $room->update($request->validate([
            'group' => 'sometimes|string',
            'adviser_id' => 'nullable|exists:users,id',
            'president_id' => 'nullable|exists:users,id',
            'secretary_id' => 'nullable|exists:users,id',
        ]));
        return response()->json($room);
    }

    public function destroy(Room $room)
    {
        $this->authorize('delete', $room);
        $room->delete();
        return response()->json(['message' => 'deleted']);
    }
}
