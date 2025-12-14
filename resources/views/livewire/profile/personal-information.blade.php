@php
    $user = $user ?? ($this->user ?? auth()->user());
    /** @var \App\Models\UserProfile $p */
    $p = $user->userProfile ?? new \App\Models\UserProfile();

    $age = null;
    if (isset($p->birthday) && $p->birthday) {
        try {
            $age = \Carbon\Carbon::parse($p->birthday)->age;
        } catch (\Throwable $e) {
            $age = null;
        }
    }

    $roleLabel = optional($user->role)->role_title ?? ($user->role_id === 4 ? 'Student' : 'Volunteer');

    // Build full name with prefix/suffix
    $fname = trim((string) ($p->f_name ?? ''));
    $mname = trim((string) ($p->m_name ?? ''));
    $sname = trim((string) ($p->s_name ?? ''));
    $genSuffix = trim((string) ($p->generational_suffix ?? ''));

    $middleInitials = '';
    if ($mname !== '') {
        $tokens = preg_split('/\s+/', $mname, -1, PREG_SPLIT_NO_EMPTY);
        $mi = [];
        foreach ($tokens as $t) {
            $first = mb_substr($t, 0, 1, 'UTF-8');
            if ($first !== '') $mi[] = mb_strtoupper($first, 'UTF-8') . '.';
        }
        $middleInitials = count($mi) ? implode(' ', $mi) : '';
    }

    $nameParts = [];
    if ($fname !== '') $nameParts[] = $fname;
    if ($middleInitials !== '') $nameParts[] = $middleInitials;
    if ($sname !== '') $nameParts[] = $sname;
    if ($genSuffix !== '') $nameParts[] = $genSuffix;
    $baseName = count($nameParts) ? implode(' ', $nameParts) : '';

    $prefixAbbr = isset($primaryProfessional) && $primaryProfessional && optional($primaryProfessional->prefix)->abbreviation ? optional($primaryProfessional->prefix)->abbreviation : null;
    $suffixTitle = isset($primaryProfessional) && $primaryProfessional && optional($primaryProfessional->suffix)->title ? optional($primaryProfessional->suffix)->title : null;

    $displayName = $baseName;
    if ($prefixAbbr) {
        $displayName = trim($prefixAbbr . ' ' . $displayName);
    }
    if ($suffixTitle) {
        $displayName = $displayName . ', ' . $suffixTitle;
    }

    // Build address
    $addressStr = '';
    if ($p && $p->address) {
        $addr = $p->address;
        $parts = [];
        if (! empty($addr->house_number)) { $parts[] = $addr->house_number; }
        if (! empty($addr->block_number)) { $parts[] = $addr->block_number; }
        if (! empty($addr->lot_number)) { $parts[] = $addr->lot_number; }
        if (! empty($addr->street)) { $parts[] = $addr->street; }
        if (! empty(optional($addr->barangay)->barangay_name)) { $parts[] = optional($addr->barangay)->barangay_name; }
        if (! empty(optional($addr->city)->city_name)) { $parts[] = optional($addr->city)->city_name; }
        if (! empty(optional($addr->province)->province_name)) { $parts[] = optional($addr->province)->province_name; }
        $addressStr = count($parts) ? implode(', ', $parts) : '';
    }

    $fullNameSubtitle = trim(collect([$p->f_name, $p->m_name, $p->s_name, $p->generational_suffix])->filter()->implode(' '));
    if (!$fullNameSubtitle) $fullNameSubtitle = $user->name ?? $user->email ?? 'User';
@endphp

<div class="profile-component-card">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title flex items-center gap-2">
                <i class="fa fa-user"></i>
                Personal Information
            </p>
            <p class="profile-card-subtitle">Of {{ $fullNameSubtitle }}</p>
        </div>
        <div class="profile-card-actions">
            @if(auth()->check() && (auth()->id() === $user->id || auth()->user()->isAdmin() || auth()->user()->isExecutive()) && Route::has('users.personal.edit'))
                <a href="{{ route('users.personal.edit', $user->id) }}" class="btn btn-ghost btn-sm gear-button" aria-label="Edit personal information">
                    <i class="fa fa-edit" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    </div>

    <div class="border-t border-slate-200 pt-3 space-y-3">
        {{-- Full Name & Address - Two Column --}}
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Full Name</p>
                <p class="text-sm font-semibold text-slate-800">{{ $displayName }}</p>
            </div>
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Address</p>
                <p class="text-sm font-semibold text-slate-800">{{ $addressStr ?: '—' }}</p>
            </div>
        </div>

        {{-- All other fields - 3x3 grid --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">First Name</p>
                <p class="text-sm font-semibold text-slate-800">{{ $p->f_name ?? '—' }}</p>
            </div>
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Middle Name</p>
                <p class="text-sm font-semibold text-slate-800">{{ $p->m_name ?? '—' }}</p>
            </div>
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Surname</p>
                <p class="text-sm font-semibold text-slate-800">{{ $p->s_name ?? '—' }}</p>
            </div>
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Generational Suffix</p>
                <p class="text-sm font-semibold text-slate-800">{{ $p->generational_suffix ?? 'N/A' }}</p>
            </div>
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Lived Name</p>
                <p class="text-sm font-semibold text-slate-800">{{ $p->lived_name ?? '—' }}</p>
            </div>
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Birthday</p>
                <p class="text-sm font-semibold text-slate-800">{{ isset($p->birthday) && $p->birthday ? \Carbon\Carbon::parse($p->birthday)->format('Y-m-d') : '—' }}</p>
            </div>
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Age</p>
                <p class="text-sm font-semibold text-slate-800">{{ $age ?? '—' }}</p>
            </div>
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Biological Sex</p>
                <p class="text-sm font-semibold text-slate-800">{{ $p->sex ? ucfirst($p->sex) : '—' }}</p>
            </div>
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Phone</p>
                <p class="text-sm font-semibold text-slate-800">{{ $p->phone_number ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>
