<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request as HttpRequest;

use App\Http\Controllers\RoomController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\PositionController;
use App\Http\Livewire\Profile\FceerProfileEdit;
use App\Http\Livewire\Profile\Guest\Edit as GuestProfileEdit;
use App\Http\Livewire\Profile\Guest\Show as GuestProfileShow;
use App\Http\Livewire\Profile\Student\Show as StudentProfileShow;
use App\Http\Livewire\Profile\Volunteer\Show as VolunteerProfileShow;

// Public homepage
Route::view('/', 'welcome');

// Education management (volunteer profile area)
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HighschoolSubjectRecord\HighschoolSubjectController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\BarangayController;
use App\Http\Controllers\HighschoolSubjectRecord\HighschoolSubjectRecordController;
use App\Http\Controllers\HighschoolRecordController;
use App\Http\Controllers\HighschoolController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\DegreeProgramController;
use App\Http\Controllers\DegreeLevelController;
use App\Http\Controllers\DegreeTypeController;
use App\Http\Controllers\DegreeFieldController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\EducationalRecordController;
use App\Http\Controllers\CommitteeMemberController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\CommitteePositionController;
use App\Http\Controllers\ProfessionalCredentialController;
use App\Http\Controllers\VolunteerSubjectController;
use App\Http\Controllers\StudentExcuseLetterController;
Route::middleware(['auth'])->group(function(){
    Route::get('/degree-programs', [\App\Http\Controllers\DegreeProgramController::class, 'index'])->name('degree-programs.index');
    Route::get('/degree-programs/create', [\App\Http\Controllers\DegreeProgramController::class, 'create'])->name('degree-programs.create');
    Route::get('/degree-programs/{degreeprogram_id}/edit', [\App\Http\Controllers\DegreeProgramController::class, 'edit'])->name('degree-programs.edit');
    Route::get('/degree-programs/{program}/show', function(\App\Models\DegreeProgram $program) {
        return view('degree-programs.show', ['program' => $program]);
    })->name('degree-programs.show');

    Route::get('/degree-levels', [\App\Http\Controllers\DegreeLevelController::class, 'index'])->name('degree-levels.index');
    Route::get('/degree-levels/create', [\App\Http\Controllers\DegreeLevelController::class, 'create'])->name('degree-levels.create');
    Route::get('/degree-levels/{degreelevel_id}/edit', [\App\Http\Controllers\DegreeLevelController::class, 'edit'])->name('degree-levels.edit');
    Route::get('/degree-levels/{level}/show', function(\App\Models\DegreeLevel $level) {
        return view('degree-levels.show', ['level' => $level]);
    })->name('degree-levels.show');

    Route::get('/degree-types', [\App\Http\Controllers\DegreeTypeController::class, 'index'])->name('degree-types.index');
    Route::get('/degree-types/create', [\App\Http\Controllers\DegreeTypeController::class, 'create'])->name('degree-types.create');
    Route::get('/degree-types/{degreetype_id}/edit', [\App\Http\Controllers\DegreeTypeController::class, 'edit'])->name('degree-types.edit');
    Route::get('/degree-types/{type}/show', function(\App\Models\DegreeType $type) {
        return view('degree-types.show', ['type' => $type]);
    })->name('degree-types.show');

    Route::get('/degree-fields', [\App\Http\Controllers\DegreeFieldController::class, 'index'])->name('degree-fields.index');
    Route::get('/degree-fields/create', [\App\Http\Controllers\DegreeFieldController::class, 'create'])->name('degree-fields.create');
    Route::get('/degree-fields/{degreefield_id}/edit', [\App\Http\Controllers\DegreeFieldController::class, 'edit'])->name('degree-fields.edit');
    Route::get('/degree-fields/{field}/show', function(\App\Models\DegreeField $field) {
        return view('degree-fields.show', ['field' => $field]);
    })->name('degree-fields.show');

    Route::get('/universities', [\App\Http\Controllers\UniversityController::class, 'index'])->name('universities.index');
    Route::get('/universities/create', [\App\Http\Controllers\UniversityController::class, 'create'])->name('universities.create');
    Route::get('/universities/{id}/edit', [\App\Http\Controllers\UniversityController::class, 'edit'])->name('universities.edit');
    Route::delete('/universities/{university}', [\App\Http\Controllers\UniversityController::class, 'destroy'])->name('universities.destroy');
    Route::get('/universities/{university}/show', function(\App\Models\University $university) {
        return view('universities.show', ['university' => $university]);
    })->name('universities.show');

    Route::get('/volunteer/{user}/educational-records', [\App\Http\Controllers\EducationalRecordController::class, 'index'])->name('educational-records.index');
    Route::get('/volunteer/{user}/educational-records/create', [\App\Http\Controllers\EducationalRecordController::class, 'create'])->name('educational-records.create');
    // Backwards-compat: old URLs included {user} in the edit path — redirect them to the new edit route
    Route::get('/volunteer/{user}/educational-records/{id}/edit', function ($user, $id) {
        return redirect()->route('educational-records.edit', ['id' => $id]);
    });

    Route::get('/volunteer/educational-records/{id}/edit', [\App\Http\Controllers\EducationalRecordController::class, 'edit'])->name('educational-records.edit');
});
Route::middleware(['auth'])->get('/profile/student/{user?}', StudentProfileShow::class)->name('profile.student.show');
Route::middleware(['auth'])->get('/profile/volunteer/{user?}', VolunteerProfileShow::class)->name('profile.volunteer.show');
Route::middleware(['auth'])->get('/profile/guest/{user?}', GuestProfileShow::class)->name('profile.guest.show');
Route::middleware(['auth'])->get('/profile/guest/{user}/edit', GuestProfileEdit::class)->name('profile.guest.edit');

// Attendance user view
Route::middleware(['auth'])->get('/attendance/user/{userId}', function($userId) {
    return view('attendance.user', ['userId' => $userId]);
})->name('attendance.user');

// Personal information edit/update (per-user)
Route::get('/users/{user}/personal/edit', [UserProfileController::class, 'editPersonalForm'])->name('users.personal.edit');
Route::patch('/users/{user}/personal', [UserProfileController::class, 'updatePersonal'])->name('users.personal.update');
// Account information edit (email/password/role/name) — uses UserController for update
Route::get('/users/{user}/account/edit', [UserProfileController::class, 'editAccountForm'])->name('users.account.edit');
Route::patch('/users/{user}/account', [\App\Http\Controllers\UserController::class, 'update'])->name('users.account.update');

    // Highschools CRUD pages (create accessible to authenticated users, edits/delete restricted in controller)
    // Master highschool subjects (create/index/edit/update/destroy) - handled by dedicated controller
    Route::post('/highschool-subjects', [HighschoolSubjectController::class, 'store'])->name('highschool_subjects.store');
    Route::get('/highschool-subjects/create', [HighschoolSubjectController::class, 'create'])->name('highschool_subjects.create');
    Route::get('/highschool-subjects', [HighschoolSubjectController::class, 'index'])->name('highschool_subjects.index');
    // Edit/update/delete for master highschool subjects (admin/executive only)
    Route::get('/highschool-subjects/{subject}/edit', [HighschoolSubjectController::class, 'edit'])->name('highschool_subjects.edit');
    Route::patch('/highschool-subjects/{subject}', [HighschoolSubjectController::class, 'update'])->name('highschool_subjects.update');
    Route::delete('/highschool-subjects/{subject}', [HighschoolSubjectController::class, 'destroy'])->name('highschool_subjects.destroy');

    // Per-user highschool records (highschool history) routes
    Route::get('/users/{user}/highschool-records/create', [HighschoolRecordController::class, 'createHighschoolRecord'])->name('users.highschool_records.create');
    Route::post('/users/{user}/highschool-records', [HighschoolSubjectRecordController::class, 'storeHighschoolRecord'])->name('users.highschool_records.store');
    Route::get('/users/{user}/highschool-records/{record}/edit', [HighschoolRecordController::class, 'editHighschoolRecord'])->name('users.highschool_records.edit');
    Route::patch('/users/{user}/highschool-records/{record}', [HighschoolRecordController::class, 'updateHighschoolRecord'])->name('users.highschool_records.update');
    Route::delete('/users/{user}/highschool-records/{record}', [HighschoolRecordController::class, 'destroyHighschoolRecord'])->name('users.highschool_records.destroy');

    Route::post('/highschools', [HighschoolController::class, 'store'])->name('highschools.store');
    Route::get('/highschools/create', [HighschoolController::class, 'create'])->name('highschools.create');
    Route::get('/highschools', [HighschoolController::class, 'index'])->name('highschools.index');
    // Edit/update/delete for master highschools (admin/executive only)
    Route::get('/highschools/{highschool}/edit', [HighschoolController::class, 'edit'])->name('highschools.edit');
    Route::patch('/highschools/{highschool}', [HighschoolController::class, 'update'])->name('highschools.update');
    Route::delete('/highschools/{highschool}', [HighschoolController::class, 'destroy'])->name('highschools.destroy');


    // Per-user student highschool subject mapping routes
    Route::get('/highschool-subject-records/create', [HighschoolSubjectRecordController::class, 'createHighschoolSubjectRecord'])->name('users.highschool_subject_records.create');
    Route::post('/users/{user}/highschool-subject-records', [HighschoolSubjectRecordController::class, 'storeHighschoolSubjectRecord'])->name('users.highschool_subject_records.store');
    Route::get('/users/{user}/highschool-subject-records/{record}/edit', [HighschoolSubjectRecordController::class, 'editHighschoolSubjectRecord'])->name('users.highschool_subject_records.edit');
    Route::patch('/users/{user}/highschool-subject-records/{record}', [HighschoolSubjectRecordController::class, 'updateHighschoolSubjectRecord'])->name('users.highschool_subject_records.update');
    Route::delete('/users/{user}/highschool-subject-records/{record}', [HighschoolSubjectRecordController::class, 'destroyHighschoolSubjectRecord'])->name('users.highschool_subject_records.destroy');

    // Backward-compatible alias used in some profile blades
    Route::get('/profile/highschool-subject-records/create/{user}', [HighschoolSubjectRecordController::class, 'createHighschoolSubjectRecord'])->name('profile.highschool_subject_records.create');


    // Committees
    Route::get('/committees', [\App\Http\Controllers\CommitteeMemberController::class, 'listCommitteesPage'])->name('committees.index');
    Route::get('/committees/create', [\App\Http\Controllers\CommitteeMemberController::class, 'createCommitteeForm'])->name('committees.create');
    Route::post('/committees', [\App\Http\Controllers\CommitteeMemberController::class, 'storeCommittee'])->name('committees.store');
    Route::get('/committees/{committee}/edit', [\App\Http\Controllers\CommitteeMemberController::class, 'editCommitteeForm'])->name('committees.edit');
    Route::patch('/committees/{committee}', [\App\Http\Controllers\CommitteeMemberController::class, 'updateCommittee'])->name('committees.update');
    Route::delete('/committees/{committee}', [\App\Http\Controllers\CommitteeMemberController::class, 'destroyCommittee'])->name('committees.destroy');

    // Rooms (Groups) - CRUD pages (controller pages embed Livewire components)
    Route::get('/rooms', [RoomController::class, 'index'])->middleware('auth')->name('rooms.index');
    Route::get('/rooms/create', [RoomController::class, 'create'])->middleware('auth')->name('rooms.create');
    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->middleware('auth')->name('rooms.edit');

    // Positions
    Route::get('/committee-positions', [\App\Http\Controllers\CommitteePositionController::class, 'index'])->name('committee_positions.index');
    Route::get('/committee-positions/create', [\App\Http\Controllers\CommitteePositionController::class, 'createForm'])->name('committee_positions.create');
    Route::post('/committee-positions', [\App\Http\Controllers\CommitteePositionController::class, 'store'])->name('committee_positions.store');
    Route::get('/committee-positions/{position}/edit', [\App\Http\Controllers\CommitteePositionController::class, 'editForm'])->name('committee_positions.edit');
    Route::patch('/committee-positions/{position}', [\App\Http\Controllers\CommitteePositionController::class, 'update'])->name('committee_positions.update');
    Route::delete('/committee-positions/{position}', [\App\Http\Controllers\CommitteePositionController::class, 'destroy'])->name('committee_positions.destroy');

    // Master Positions (UI) - create a master Positions UI that maps to committee_positions
    Route::get('/positions', function () { return view('positions.index'); })->middleware('auth')->name('positions.index');
    Route::get('/positions/create', function () { return view('positions.create'); })->middleware('auth')->name('positions.create');
    Route::get('/positions/{id}/edit', function ($id) { return view('positions.edit'); })->middleware('auth')->name('positions.edit');

    // PositionController endpoints (data API) - these perform create/update/delete and list data
    Route::middleware('auth')->group(function () {
        Route::get('/positions/list', [PositionController::class, 'index'])->name('positions.list');
        Route::get('/positions/create/form', [PositionController::class, 'createForm'])->name('positions.createForm');
        Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
        Route::get('/positions/{id}/edit/form', [PositionController::class, 'editForm'])->name('positions.editForm');
        Route::patch('/positions/{id}', [PositionController::class, 'update'])->name('positions.update');
        Route::delete('/positions/{id}', [PositionController::class, 'destroy'])->name('positions.destroy');
    });

    // Cities and Barangays (master data web CRUD)
    Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
    Route::get('/cities/create', [CityController::class, 'create'])->name('cities.create');
    Route::post('/cities', [CityController::class, 'store'])->name('cities.store');
    // explicit redirects to avoid accidental matching of /cities/{city} when a relative link
    // like "provinces" is clicked from /cities (which would become /cities/provinces and
    // be captured by the /cities/{city} placeholder). These ensure a safe redirect.
    Route::get('/cities/provinces', function () { return redirect()->route('provinces.index'); });
    Route::get('/cities/provinces/create', function () { return redirect()->route('provinces.create'); });
    Route::get('/cities/{city}/edit', [CityController::class, 'edit'])->name('cities.edit');
    Route::patch('/cities/{city}', [CityController::class, 'update'])->name('cities.update');
    Route::delete('/cities/{city}', [CityController::class, 'destroy'])->name('cities.destroy');

    // Provinces
    Route::get('/provinces', [ProvinceController::class, 'index'])->name('provinces.index');
    Route::get('/provinces/create', [ProvinceController::class, 'create'])->name('provinces.create');
    Route::post('/provinces', [ProvinceController::class, 'store'])->name('provinces.store');
    Route::get('/provinces/{province}/edit', [ProvinceController::class, 'edit'])->name('provinces.edit');
    Route::patch('/provinces/{province}', [ProvinceController::class, 'update'])->name('provinces.update');
    Route::delete('/provinces/{province}', [ProvinceController::class, 'destroy'])->name('provinces.destroy');

    Route::get('/barangays', [BarangayController::class, 'index'])->name('barangays.index');
    Route::get('/barangays/create', [BarangayController::class, 'create'])->name('barangays.create');
    Route::post('/barangays', [BarangayController::class, 'store'])->name('barangays.store');
    Route::get('/barangays/{barangay}/edit', [BarangayController::class, 'edit'])->name('barangays.edit');
    Route::patch('/barangays/{barangay}', [BarangayController::class, 'update'])->name('barangays.update');
    Route::delete('/barangays/{barangay}', [BarangayController::class, 'destroy'])->name('barangays.destroy');


    // Field of work / Prefix / Suffix web forms (for professional credentials)
    Route::get('/fields-of-work/create', [\App\Http\Controllers\ProfessionalCredentialController::class, 'createFieldForm'])->name('fields_of_work.create');
    Route::post('/fields-of-work', [\App\Http\Controllers\ProfessionalCredentialController::class, 'storeFieldForm'])->name('fields_of_work.store');
    Route::get('/fields-of-work', [\App\Http\Controllers\ProfessionalCredentialController::class, 'indexFieldsPage'])->name('fields_of_work.index');
    Route::get('/fields-of-work/{field}/edit', [\App\Http\Controllers\ProfessionalCredentialController::class, 'editFieldForm'])->name('fields_of_work.edit');
    Route::patch('/fields-of-work/{field}', [\App\Http\Controllers\ProfessionalCredentialController::class, 'updateFieldForm'])->name('fields_of_work.update');
    Route::delete('/fields-of-work/{field}', [\App\Http\Controllers\ProfessionalCredentialController::class, 'destroyFieldForm'])->name('fields_of_work.destroy');

    Route::get('/prefixes/create', [\App\Http\Controllers\ProfessionalCredentialController::class, 'createPrefixForm'])->name('prefixes.create');
    Route::post('/prefixes', [\App\Http\Controllers\ProfessionalCredentialController::class, 'storePrefixForm'])->name('prefixes.store');
    Route::get('/prefixes', [\App\Http\Controllers\ProfessionalCredentialController::class, 'indexPrefixesPage'])->name('prefixes.index');
    Route::get('/prefixes/{prefix}/edit', [\App\Http\Controllers\ProfessionalCredentialController::class, 'editPrefixForm'])->name('prefixes.edit');
    Route::patch('/prefixes/{prefix}', [\App\Http\Controllers\ProfessionalCredentialController::class, 'updatePrefixForm'])->name('prefixes.update');
    Route::delete('/prefixes/{prefix}', [\App\Http\Controllers\ProfessionalCredentialController::class, 'destroyPrefixForm'])->name('prefixes.destroy');

    Route::get('/suffixes/create', [\App\Http\Controllers\ProfessionalCredentialController::class, 'createSuffixForm'])->name('suffixes.create');
    Route::post('/suffixes', [\App\Http\Controllers\ProfessionalCredentialController::class, 'storeSuffixForm'])->name('suffixes.store');
    Route::get('/suffixes', [\App\Http\Controllers\ProfessionalCredentialController::class, 'indexSuffixesPage'])->name('suffixes.index');
    Route::get('/suffixes/{suffix}/edit', [\App\Http\Controllers\ProfessionalCredentialController::class, 'editSuffixForm'])->name('suffixes.edit');
    Route::patch('/suffixes/{suffix}', [\App\Http\Controllers\ProfessionalCredentialController::class, 'updateSuffixForm'])->name('suffixes.update');
    Route::delete('/suffixes/{suffix}', [\App\Http\Controllers\ProfessionalCredentialController::class, 'destroySuffixForm'])->name('suffixes.destroy');

    // Admin overview for professional credentials
    Route::get('/professional-credentials', [\App\Http\Controllers\ProfessionalCredentialController::class, 'indexProfessionalCredentials'])->name('professional_credentials.index');

    // Per-user professional credential forms (profile-scoped)
    Route::get('/profile/{user}/professional-credentials/create', [\App\Http\Controllers\ProfessionalCredentialController::class, 'createProfessionalCredential'])->name('profile.professional_credentials.create');
    Route::post('/profile/{user}/professional-credentials', [\App\Http\Controllers\ProfessionalCredentialController::class, 'storeProfessionalCredential'])->name('profile.professional_credentials.store');
    Route::get('/profile/{user}/professional-credentials/{credential}/edit', [\App\Http\Controllers\ProfessionalCredentialController::class, 'editProfessionalCredential'])->name('profile.professional_credentials.edit');
    Route::patch('/profile/{user}/professional-credentials/{credential}', [\App\Http\Controllers\ProfessionalCredentialController::class, 'updateProfessionalCredential'])->name('profile.professional_credentials.update');
    Route::delete('/profile/{user}/professional-credentials/{credential}', [\App\Http\Controllers\ProfessionalCredentialController::class, 'destroyProfessionalCredential'])->name('profile.professional_credentials.destroy');
    // Volunteer subjects (master list) - admin/executive only
    Route::get('/volunteer-subjects', [\App\Http\Controllers\VolunteerSubjectController::class, 'index'])->name('volunteer_subjects.index');
    Route::get('/volunteer-subjects/create', [\App\Http\Controllers\VolunteerSubjectController::class, 'create'])->name('volunteer_subjects.create');
    Route::post('/volunteer-subjects', [\App\Http\Controllers\VolunteerSubjectController::class, 'store'])->name('volunteer_subjects.store');
    Route::get('/volunteer-subjects/{subject}/edit', [\App\Http\Controllers\VolunteerSubjectController::class, 'edit'])->name('volunteer_subjects.edit');
    Route::patch('/volunteer-subjects/{subject}', [\App\Http\Controllers\VolunteerSubjectController::class, 'update'])->name('volunteer_subjects.update');
    Route::delete('/volunteer-subjects/{subject}', [\App\Http\Controllers\VolunteerSubjectController::class, 'destroy'])->name('volunteer_subjects.destroy');

// Public sample form page
Route::view('/forms/sample', 'forms.sample')->name('forms.sample');

// User profile public/show route (requires auth for viewing details)
// Logged-in user's account/profile page
// Redirect to the appropriate Livewire profile page (student or volunteer)
Route::get('/account', function () {
    $user = Auth::user();
    if (! $user) {
        abort(403);
    }
    $roleTitle = optional($user->role)->role_title;
    $roleId = (int) ($user->role_id ?? 0);
    $guestRoleId = (int) config('roles.guest_id', 5);

    // Prefer volunteer-specific page for volunteers, guest page for guests, fallback to student page
    if ($roleTitle === 'Volunteer' || in_array($roleId, [1,2,3], true)) {
        return redirect()->route('profile.volunteer.show', ['user' => $user->id]);
    }

    if ($roleTitle === 'Guest' || $roleId === $guestRoleId) {
        return redirect()->route('profile.guest.show', ['user' => $user->id]);
    }

    return redirect()->route('profile.student.show', ['user' => $user->id]);
})->middleware('auth')->name('profile.show');

// View another user's profile (admins/executives) or any authenticated view
// Redirect to the appropriate Livewire profile page depending on the target user's role
Route::get('/profile/student', function (\App\Models\User $user) {
    $roleId = (int) ($user->role_id ?? 0);
    $roleTitle = optional($user->role)->role_title;
    $guestRoleId = (int) config('roles.guest_id', 5);
    if (in_array($roleId, [1,2,3], true) || $roleTitle === 'Volunteer') {
        return redirect()->route('profile.volunteer.show', ['user' => $user->id]);
    }
    if ($roleId === $guestRoleId || $roleTitle === 'Guest') {
        return redirect()->route('profile.guest.show', ['user' => $user->id]);
    }
    return redirect()->route('profile.student.show', ['user' => $user->id]);
})->middleware('auth')->name('users.profile.show');

require __DIR__.'/auth.php';

// FCEER Profile show/edit routes
use App\Http\Livewire\Roster;
Route::middleware(['auth'])->group(function() {
    // Roster pages (Admins/Executives only - components enforce authorization)
    // Serve Blade wrappers that embed the Livewire roster components
    Route::get('/roster/volunteers', [\App\Http\Controllers\RosterController::class, 'index'])->name('roster.volunteers.index');
    Route::get('/roster/students', [\App\Http\Controllers\RosterController::class, 'studentsIndex'])->name('roster.students.index');
    Route::get('/roster/users', \App\Http\Livewire\Roster\Users::class)->name('roster.users.index');
    Route::get('/users/{user}/fceer-profile/edit', FceerProfileEdit::class)
        ->name('fceer-profile.edit');
});

// Ensure a logout route exists that properly logs out and redirects to the welcome page.
Route::post('/logout', function (HttpRequest $request) {    
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Profile-scoped committee management routes (web UI pages)
Route::middleware(['auth'])->group(function () {
    // Committees (profile area)
    Route::get('/profile/committees', [\App\Http\Controllers\CommitteeController::class, 'index'])->name('profile.committees.index');
    Route::get('/profile/committees/create', [\App\Http\Controllers\CommitteeController::class, 'createForm'])->name('profile.committees.create');
    Route::post('/profile/committees', [\App\Http\Controllers\CommitteeController::class, 'store'])->name('profile.committees.store');
    Route::get('/profile/committees/{committee}/edit', [\App\Http\Controllers\CommitteeController::class, 'editForm'])->name('profile.committees.edit');
    Route::patch('/profile/committees/{committee}', [\App\Http\Controllers\CommitteeController::class, 'update'])->name('profile.committees.update');
    Route::delete('/profile/committees/{committee}', [\App\Http\Controllers\CommitteeController::class, 'destroy'])->name('profile.committees.destroy');

    // Committee positions (profile area)
    Route::get('/profile/committee-positions', [\App\Http\Controllers\CommitteePositionController::class, 'index'])->name('profile.committee_positions.index');
    Route::get('/profile/committee-positions/create', [\App\Http\Controllers\CommitteePositionController::class, 'createForm'])->name('profile.committee_positions.create');
    Route::post('/profile/committee-positions', [\App\Http\Controllers\CommitteePositionController::class, 'store'])->name('profile.committee_positions.store');
    Route::get('/profile/committee-positions/{position}/edit', [\App\Http\Controllers\CommitteePositionController::class, 'editForm'])->name('profile.committee_positions.edit');
    Route::patch('/profile/committee-positions/{position}', [\App\Http\Controllers\CommitteePositionController::class, 'update'])->name('profile.committee_positions.update');
    Route::delete('/profile/committee-positions/{position}', [\App\Http\Controllers\CommitteePositionController::class, 'destroy'])->name('profile.committee_positions.destroy');

    // Committee members (profile area)
    Route::get('/profile/volunteer/committee-members', [\App\Http\Controllers\CommitteeMemberController::class, 'indexMembers'])->name('profile.volunteer.committee_members.index');
    Route::get('/profile/volunteer/committee-members/create/{user}', [\App\Http\Controllers\CommitteeMemberController::class, 'createMemberForUser'])->name('profile.volunteer.committee_members.create');
    Route::post('/profile/volunteer/committee-members/{user}', [\App\Http\Controllers\CommitteeMemberController::class, 'storeMemberForUser'])->name('profile.volunteer.committee_members.store');
    Route::get('/profile/volunteer/committee-members/{id}/edit', [\App\Http\Controllers\CommitteeMemberController::class, 'editMemberForm'])->name('profile.volunteer.committee_members.edit');
    Route::patch('/profile/volunteer/committee-members/{id}', [\App\Http\Controllers\CommitteeMemberController::class, 'updateMember'])->name('profile.volunteer.committee_members.update');
    Route::delete('/profile/volunteer/committee-members/{id}', [\App\Http\Controllers\CommitteeMemberController::class, 'destroyMember'])->name('profile.volunteer.committee_members.destroy');
    // Subject teacher mappings (per-user profile area)
    Route::get('/profile/subject-teachers/create/{user}', [\App\Http\Controllers\SubjectTeacherController::class, 'create'])->name('profile.subject_teachers.create');
    Route::post('/profile/subject-teachers/{user}', [\App\Http\Controllers\SubjectTeacherController::class, 'store'])->name('profile.subject_teachers.store');
    Route::get('/profile/subject-teachers/{teacher}/edit', [\App\Http\Controllers\SubjectTeacherController::class, 'edit'])->name('profile.subject_teachers.edit');
    Route::patch('/profile/subject-teachers/{teacher}', [\App\Http\Controllers\SubjectTeacherController::class, 'update'])->name('profile.subject_teachers.update');
    Route::delete('/profile/subject-teachers/{teacher}', [\App\Http\Controllers\SubjectTeacherController::class, 'destroy'])->name('profile.subject_teachers.destroy');
});

// FCEER Profile and Room routes


// Room management (admin/exec only for create/edit)
// Routes for rooms are handled by the controller above (pages embed Livewire components).

// Attendance pages (authenticated)
Route::middleware(['auth'])->group(function() {
    Route::get('/attendance/volunteers', function () {
        return view('attendance.volunteers');
    })->name('attendance.volunteers');

    Route::get('/attendance/students', function () {
        return view('attendance.students');
    })->name('attendance.students');

    // Create attendance page (optionally for a specific user)
    Route::get('/attendance/create/{user?}', function ($user = null) {
        return view('attendance.create', ['userId' => $user]);
    })->name('attendance.create');

    // Edit attendance page (attendance id)
    Route::get('/attendance/{attendance}/edit', function ($attendance) {
        return view('attendance.edit', ['attendanceId' => is_object($attendance) ? ($attendance->attendance_id ?? null) : $attendance]);
    })->name('attendance.edit');

    // Student Excuse Letters
    Route::get('/student/{user_id}/excuse-letters', [StudentExcuseLetterController::class, 'index'])->name('student.excuse-letters.index');
    Route::get('/student/{user_id}/excuse-letters/create', [StudentExcuseLetterController::class, 'create'])->name('student.excuse-letters.create');
    Route::post('/student/{user_id}/excuse-letters', [StudentExcuseLetterController::class, 'store'])->name('student.excuse-letters.store');
    Route::get('/student/{user_id}/excuse-letters/{letter_id}', [StudentExcuseLetterController::class, 'show'])->name('student.excuse-letters.show');
    Route::get('/student/{user_id}/excuse-letters/{letter_id}/edit', [StudentExcuseLetterController::class, 'edit'])->name('student.excuse-letters.edit');
    Route::put('/student/{user_id}/excuse-letters/{letter_id}', [StudentExcuseLetterController::class, 'update'])->name('student.excuse-letters.update');
    Route::delete('/student/{user_id}/excuse-letters/{letter_id}', [StudentExcuseLetterController::class, 'destroy'])->name('student.excuse-letters.destroy');
    Route::get('/download/excuse-letter/{id}', [StudentExcuseLetterController::class, 'download'])->name('download.excuse-letter');
});

// FCEER Profile per user (student/volunteer) - use Livewire string name for route
Route::middleware(['auth'])->get('/profile/fceer/{user}', function($user) {
    return view('livewire.profile.fceer-profile-route', ['userId' => $user]);
})->name('profile.fceer.show');
