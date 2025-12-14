<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserProfileRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Address;
use App\Models\User;

class UserProfileController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', UserProfile::class);
        return response()->json(UserProfile::with('user','address','attendanceStatus','batch')->paginate(20));
    }

    /**
     * Show the personal information edit form for a user.
     * - users can edit their own personal info
     * - Admins and Executives can edit any user's personal info
     */
    public function editPersonalForm(User $user)
    {
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        if (! $actor->canEditUserProfile($user)) {
            abort(403);
        }

        $profile = $user->userProfile ?? new UserProfile(['user_id' => $user->id]);
        $cities = \App\Models\City::orderBy('city_name')->get();
        $barangays = \App\Models\Barangay::orderBy('barangay_name')->get();

        return view('users.personal_edit', compact('user','profile','cities','barangays'));
    }

    /**
     * Show the account information edit form for a user (email/password/role/name).
     * Uses the same authorization rules as personal info editing.
     */
    public function editAccountForm(User $user)
    {
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        if (! $actor->canEditUserProfile($user)) {
            abort(403);
        }

        return view('users.account_edit', compact('user'));
    }

    /**
     * Handle update of personal information from the edit form.
     */
    public function updatePersonal(UpdateUserProfileRequest $request, User $user)
    {
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        if (! $actor->canEditUserProfile($user)) {
            abort(403);
        }

        $data = $request->validated();

        // Handle address creation/update if individual address fields were provided
    $addressFields = array_intersect_key($data, array_flip(['house_number','street','barangay_id','city_id','province_id']));

        $profile = $user->userProfile;

        if ($profile) {
            // If an explicit address_id was provided, prefer that
            if (! empty($data['address_id'])) {
                $profile->address_id = $data['address_id'];
            } elseif (! empty($addressFields)) {
                if ($profile->address) {
                    $profile->address->update($addressFields);
                } else {
                    $addr = Address::create($addressFields);
                    $profile->address_id = $addr->address_id;
                }
            }

            // Update other profile fields
            $profile->fill(array_diff_key($data, array_flip(['house_number','street','barangay_id','city_id','province_id','address_id'])));
            $profile->save();
        } else {
            // Create profile, possibly creating an address first
            if (! empty($data['address_id'])) {
                $data['address_id'] = $data['address_id'];
            } elseif (! empty($addressFields)) {
                $addr = Address::create($addressFields);
                $data['address_id'] = $addr->address_id;
            }

            $data['user_id'] = $user->id;
            $profile = UserProfile::create($data);
        }

        $roleId = $user->role_id ?? ($user->role->role_id ?? null);
        if (in_array($roleId, [1,2,3], true)) {
            return redirect()->route('profile.volunteer.show', ['user' => $user->id])->with('status', 'Personal information updated');
        }

        return redirect()->route('profile.student.show', ['user' => $user->id])->with('status', 'Personal information updated');
    }

    public function show(UserProfile $user_profile)
    {
        $this->authorize('view', $user_profile);
        return response()->json($user_profile->load('user','address','attendanceStatus','batch'));
    }

    public function store(StoreUserProfileRequest $request)
    {
        $this->authorize('create', UserProfile::class);
        $profile = UserProfile::create($request->validated());
        return response()->json($profile, 201);
    }

    public function update(UpdateUserProfileRequest $request, UserProfile $user_profile)
    {
    /** @var \App\Models\User $actor */
    $actor = Auth::user();
    if (! $actor->canEditUserProfile($user_profile->user)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user_profile->update($request->validated());
        return response()->json($user_profile);
    }

    public function destroy(UserProfile $user_profile)
    {
    /** @var \App\Models\User $actor */
    $actor = Auth::user();
    if (! $actor->isStaff()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user_profile->delete();
        return response()->json(['message' => 'deleted']);
    }
}
