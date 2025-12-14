@php
    $displayName = $user->full_name ?? $user->name ?? optional($user->userProfile)->full_name ?? $user->username ?? $user->email ?? 'User';
    $cleanName = trim(preg_replace('/\s+/', ' ', preg_replace('/[^A-Za-z0-9\s]/', '', $displayName ?? '')));
    $initials = collect(explode(' ', $cleanName))->filter()->take(2)->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))->implode('');
    $roleId = optional($user)->role_id;
    $roleLabel = $user->role?->role_title ?? ($roleId ? ucwords(str_replace(['_', '-'], ' ', array_search((int) $roleId, config('roles', []) ?? []) ?: '')) : null);
    $roleLabel = $roleLabel ?: ($roleId === 4 ? 'Student' : 'Volunteer');
@endphp

<div class="profile-page">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

    <div class="profile-grid-wrapper">
        <div class="grid gap-6 sm:grid-cols-[220px_1fr]">
            <aside class="bg-transparent space-y-4">
                <div class="flex flex-col items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-200 text-2xl font-semibold text-slate-700">
                        {{ $initials ?: '' }}
                    </div>
                    <div class="text-sm font-semibold text-slate-900">{{ $displayName }}</div>
                    <div class="text-[10px] uppercase tracking-[0.3em] text-slate-500">{{ $roleLabel }}</div>
                </div>

                <nav class="profile-nav-column text-sm font-medium text-slate-700" aria-label="Profile navigation">
                    <a href="#account" class="profile-nav-item whitespace-nowrap" data-target="account">Account Records</a>
                    <a href="#fceer" class="profile-nav-item whitespace-nowrap" data-target="fceer">FCEER Records</a>
                    <a href="#personal" class="profile-nav-item whitespace-nowrap" data-target="personal">Personal Records</a>
                </nav>
            </aside>

            <div class="profile-main-card">
                {{-- Account Records Section --}}
                <div class="hidden" id="account">
                    @livewire('profile.account-information', ['userId' => $user->id])
                </div>

                {{-- FCEER Records Section --}}
                <div class="hidden space-y-4" id="fceer">
                    @livewire('profile.fceer-profile', ['userId' => $user->id, 'role' => 'volunteer'])
                    @livewire('profile.volunteer.subject-teachers.all', ['userId' => $user->id])
                    @livewire('profile.volunteer.committee-members.index', ['userId' => $user->id])
                </div>

                {{-- Personal Records Section --}}
                <div class="hidden space-4" id="personal">
                    @livewire('profile.personal-information', ['userId' => $user->id])
                    @livewire('profile.highschool-records.all', ['userId' => $user->id])
                    @if(in_array($roleId, [1, 2, 3], true))
                        @livewire('profile.volunteer.educational-records.all', ['userId' => $user->id])
                        @livewire('profile.volunteer.professional-credentials.all', ['userId' => $user->id])
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const navItems = document.querySelectorAll('.profile-nav-item');
        const sections = ['account', 'fceer', 'personal'];

        function activate(target) {
            sections.forEach((id) => {
                const el = document.getElementById(id);
                if (el) el.classList.toggle('hidden', id !== target);
            });

            navItems.forEach((item) => {
                const dt = (item.getAttribute('data-target') || item.getAttribute('href') || '').replace(/^#/, '');
                item.classList.toggle('active', dt === target);
            });
        }

        function setup() {
            navItems.forEach((item) => {
                item.addEventListener('click', (event) => {
                    event.preventDefault();
                    const target = (item.getAttribute('data-target') || item.getAttribute('href') || '').replace(/^#/, '');
                    activate(target);
                    history.replaceState(null, '', '#' + target);
                });
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            setup();
            const hash = location.hash.replace('#', '') || 'account';
            activate(sections.includes(hash) ? hash : 'account');
        });
    })();
</script>
