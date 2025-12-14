@php
    $user = $user ?? ($this->user ?? auth()->user());
    $userId = $userId ?? ($user->id ?? auth()->id());
    $roleLabel = optional($user->role)->role_title ?? ($user->role_id === 4 ? 'Student' : 'Volunteer');
    $p = $user->userProfile ?? null;
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
                <i class="fa fa-certificate"></i>
                Professional Credentials
            </p>
            <p class="profile-card-subtitle">Of {{ $fullName }}</p>
        </div>
        <div class="profile-card-actions">
            @if(auth()->check() && (auth()->id() === $userId || auth()->user()->isAdmin() || auth()->user()->isExecutive()))
                <a href="{{ route('profile.professional_credentials.create', $userId) }}" class="btn btn-ghost btn-sm gear-button" aria-label="Add professional credential"><i class="fa fa-plus" aria-hidden="true"></i></a>
            @endif
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($creds ?? [] as $c)
                @php
                    $prefixSuffix = collect([
                        optional($c->prefix)->abbreviation ?? optional($c->prefix)->title,
                        optional($c->suffix)->abbreviation ?? optional($c->suffix)->title
                    ])->filter()->implode(' / ');
                @endphp
                <a href="{{ route('profile.professional_credentials.edit', ['user'=>$userId,'credential'=>$c->credential_id]) }}" class="block">
                    <div class="space-y-1 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 hover:bg-slate-100 hover:border-slate-200 transition">
                        <p class="text-base font-semibold text-slate-900">{{ optional($c->fieldOfWork)->name ?? '—' }}</p>
                        @if($prefixSuffix)
                            <p class="text-sm text-slate-600">{{ $prefixSuffix }}</p>
                        @endif
                        <p class="text-xs text-slate-400">{{ $c->issued_on ? (is_string($c->issued_on) ? $c->issued_on : $c->issued_on->format('Y')) : '—' }}</p>
                        @if($c->notes)
                            <p class="text-xs text-slate-400">{{ Str::limit($c->notes, 60) }}</p>
                        @endif
                    </div>
                </a>
            @empty
                <div class="text-sm text-slate-500 py-4 col-span-full">No credentials found.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-3">{{ ($creds ?? collect())->links() }}</div>
</div>
