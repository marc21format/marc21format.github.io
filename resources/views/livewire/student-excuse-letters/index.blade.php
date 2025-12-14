<div class="profile-component-card">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Excuse Letters</p>
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
            <a href="{{ route('student.excuse-letters.create', ['user_id' => $user->id]) }}" class="gear-button text-slate-800" title="Create">
                <i class="fa fa-plus" aria-hidden="true"></i>
            </a>
        </div>
    </div>
    <div class="border-t border-slate-200 pt-4">
        @if($excuseLetters->count() > 0)
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-2 px-3 font-medium text-slate-600">#</th>
                        <th class="text-left py-2 px-3 font-medium text-slate-600">Date Attendance</th>
                        <th class="text-left py-2 px-3 font-medium text-slate-600">Reason</th>
                        <th class="text-left py-2 px-3 font-medium text-slate-600">Status</th>
                        <th class="text-left py-2 px-3 font-medium text-slate-600">Submitted</th>
                        <th class="text-left py-2 px-3 font-medium text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($excuseLetters as $index => $letter)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="py-2 px-3">{{ $index + 1 }}</td>
                            <td class="py-2 px-3">{{ $letter->date_attendance->format('M d, Y') }}</td>
                            <td class="py-2 px-3">{{ $letter->reason }}</td>
                            <td class="py-2 px-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $letter->status === 'approved' || $letter->status === 'received' ? 'bg-green-100 text-green-800' : ($letter->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $letter->status === 'approved' ? 'Received' : ucfirst($letter->status) }}
                                </span>
                            </td>
                            <td class="py-2 px-3">{{ $letter->created_at->format('M d, Y H:i') }}</td>
                            <td class="py-2 px-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('student.excuse-letters.show', ['user_id' => $user->id, 'letter_id' => $letter->letter_id]) }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('student.excuse-letters.edit', ['user_id' => $user->id, 'letter_id' => $letter->letter_id]) }}" class="text-green-600 hover:text-blue-800">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('student.excuse-letters.destroy', ['user_id' => $user->id, 'letter_id' => $letter->letter_id]) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure?')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-slate-500 text-center py-4">No excuse letters found.</p>
        @endif
    </div>
</div>