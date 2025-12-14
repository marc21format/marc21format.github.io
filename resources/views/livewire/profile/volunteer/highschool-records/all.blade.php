@php
    $user = $user ?? ($this->user ?? auth()->user());
    $hsUserId = $userId ?? ($user->id ?? auth()->id());

    if (! isset($records)) {
        try {
            $records = \App\Models\HighschoolRecord::where('user_id', $hsUserId)
                ->orderBy('year_end', 'desc')
                ->paginate(10);
        } catch (\Throwable $e) {
            $records = collect();
        }
    }

    $roleLabel = optional($user->role)->role_title ?? ($user->role_id === 4 ? 'Student' : 'Volunteer');
@endphp

<div class="profile-component-card">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-meta">{{ strtoupper($roleLabel) }} HIGHSCHOOL RECORDS</p>
            <p class="profile-card-title">Highschool Records</p>
            <p class="profile-card-subtitle">{{ strtoupper($roleLabel) }}</p>
        </div>
        <div class="profile-card-actions">
            @if(auth()->check() && (auth()->id() === $hsUserId || auth()->user()->isAdmin() || auth()->user()->isExecutive()) && Route::has('users.highschool_records.create'))
                <a href="{{ route('users.highschool_records.create', $hsUserId) }}" class="btn btn-ghost btn-sm gear-button" aria-label="Add highschool record"><i class="fa fa-plus" aria-hidden="true"></i></a>
            @endif
        </div>
    </div>
    <div class="profile-summary">
        @if(isset($records) && $records->count())
            <table class="profile-table table-vertical">
                <thead>
                    <tr>
                        <th class="px-4 py-2">School</th>
                        <th class="px-4 py-2">Level</th>
                        <th class="px-4 py-2">Year Start</th>
                        <th class="px-4 py-2">Year Graduated</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                        @php $editUrl = route('users.highschool_records.edit', ['user' => $hsUserId, 'record' => $record->record_id]); @endphp
                        <tr>
                            <td>
                                <a href="{{ $editUrl }}" class="profile-link">{{ optional($record->highschool)->highschool_name ?? '—' }}</a>
                            </td>
                            <td>{{ $record->level }}</td>
                            <td>{{ $record->year_start }}</td>
                            <td>{{ $record->year_end }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ method_exists($records, 'links') ? $records->links() : '' }}
            </div>
        @else
            <div class="profile-table">No highschool records.</div>
        @endif
    </div>
</div>

