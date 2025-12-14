<div class="profile-component-card">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Excuse Letter Details</p>
            @php
                $prefix = optional(optional(optional($user)->professionalCredentials->last())->prefix)->title ?? '';
                $f = optional(optional($user)->userProfile)->f_name ?? optional($user)->name ?? '';
                $m = optional(optional($user)->userProfile)->m_name ?? '';
                $l = optional(optional($user)->userProfile)->l_name ?? '';
                $gen = optional(optional($user)->userProfile)->generational_suffix ?? optional(optional(optional($user)->professionalCredentials->last())->suffix)->title ?? '';
                $creds = optional($user)->professionalCredentials ? optional($user)->professionalCredentials->pluck('credential_title')->filter()->join(', ') : '';
                $displayName = trim(trim($prefix).' '.trim($f).' '.trim($m).' '.trim($l));
            @endphp
            <p class="profile-card-subtitle">{{ $displayName ?: (optional($user)->email ?? 'User') }}@if($gen) , {{ $gen }}@endif@if($creds) — {{ $creds }}@endif</p>
        </div>
        <div class="profile-card-actions">
            <a href="{{ route('student.excuse-letters.edit', ['user_id' => $user->id, 'letter_id' => $letter->letter_id]) }}" class="gear-button text-slate-800" title="Edit">
                <i class="fa fa-edit" aria-hidden="true"></i>
            </a>
            <form method="POST" action="{{ route('student.excuse-letters.destroy', ['user_id' => $user->id, 'letter_id' => $letter->letter_id]) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="gear-button text-slate-800" title="Delete" onclick="return confirm('Are you sure?')">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </button>
            </form>
            <a href="{{ route('student.excuse-letters.index', ['user_id' => $user->id]) }}" class="gear-button text-slate-800" title="Back">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <div class="space-y-4">
            <div class="form-group">
                <label class="form-label">Reason</label>
                <div class="text-slate-800">{{ $letter->reason }}</div>
            </div>
            <div class="form-group">
                <label class="form-label">Date of Attendance</label>
                <div class="text-slate-800">{{ $letter->date_attendance->format('F d, Y') }}</div>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <span class="px-2 py-1 text-xs rounded-full {{ $letter->status === 'approved' || $letter->status === 'received' ? 'bg-green-100 text-green-800' : ($letter->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ $letter->status === 'approved' ? 'Received' : ucfirst($letter->status) }}
                </span>
            </div>
            <div class="form-group">
                <label class="form-label">Submitted At</label>
                <div class="text-slate-800">{{ $letter->created_at->format('F d, Y H:i') }}</div>
            </div>
            @if($letter->letter_link)
                <div class="form-group">
                    <label class="form-label">Attached File</label>
                    <a href="{{ route('download.excuse-letter', $letter->letter_id) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                        <i class="fa fa-download"></i> Download {{ basename($letter->letter_link) }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>