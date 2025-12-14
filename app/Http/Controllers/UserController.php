<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);
        return response()->json(User::with('role','userProfile')->paginate(20));
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);
        return response()->json($user->load('role','userProfile'));
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);
        return response()->json($user, 201);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
    /** @var \App\Models\User $actor */
    $actor = Auth::user();
        if (! $actor->canEditUserProfile($user)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validated();
        if (! empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return response()->json($user);
    }

    public function destroy(User $user)
    {
    /** @var \App\Models\User $actor */
    $actor = Auth::user();
        if (! $actor->isStaff()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'deleted']);
    }
}
