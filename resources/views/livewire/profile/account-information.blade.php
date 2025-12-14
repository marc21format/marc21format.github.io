@php
    $user = $user ?? ($this->user ?? auth()->user());
    $roleLabel = optional($user->role)->role_title ?? ($user->role_id === 4 ? 'Student' : 'Volunteer');
    $p = $user->userProfile ?? null;
    $username = $user->name;
    $fullName = trim(collect([
        optional($p)->f_name,
        optional($p)->m_name,
        optional($p)->s_name,
        optional($p)->generational_suffix
    ])->filter()->implode(' '));
    if (!$fullName) $fullName = $user->name ?? $user->email ?? 'User';
@endphp

<div class="profile-component-card">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title flex items-center gap-2">
                <i class="fa fa-id-card"></i>
                Account Information
            </p>
            <p class="profile-card-subtitle">Of {{ $username }}</p>
        </div>
        <div class="profile-card-actions">
            @if(auth()->check() && (auth()->id() === $user->id || auth()->user()->isAdmin() || auth()->user()->isExecutive()) && Route::has('users.account.edit'))
                <a href="{{ route('users.account.edit', $user->id) }}" class="btn btn-ghost btn-sm gear-button" aria-label="Edit account information">
                    <i class="fa fa-edit" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    </div>
    <div class="border-t border-slate-200 pt-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Email</p>
                <p class="text-sm font-semibold text-slate-800">{{ $user->email ?? '-' }}</p>
            </div>
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Username</p>
                <p class="text-sm font-semibold text-slate-800">{{ $user->name ?? trim((($user->userProfile->f_name ?? '') . ' ' . ($user->userProfile->s_name ?? ''))) }}</p>
            </div>
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Role</p>
                <p class="text-sm font-semibold text-slate-800">{{ $user->role->role_title ?? '-' }}</p>
            </div>
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Created At</p>
                <p class="text-sm font-semibold text-slate-800">{{ $user->created_at ? $user->created_at->format('M d, Y') : '-' }}</p>
            </div>
        </div>
    </div>
</div>
