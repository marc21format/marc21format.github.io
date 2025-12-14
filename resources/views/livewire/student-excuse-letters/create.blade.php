<div class="profile-component-card">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Create Excuse Letter</p>
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
            <button type="button" class="gear-button text-slate-800" wire:click.prevent="save" title="Save">
                <i class="fa fa-check" aria-hidden="true"></i>
            </button>
            <a href="{{ route('student.excuse-letters.index', ['user_id' => $user->id]) }}" class="gear-button text-slate-800" title="Cancel">
                <i class="fa fa-times" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <form wire:submit.prevent="save" enctype="multipart/form-data">
            <div class="form-group">
                <label for="reason" class="form-label">Reason</label>
                <textarea wire:model="reason" id="reason" class="form-control" rows="3" placeholder="Enter the reason for the excuse"></textarea>
                @error('reason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                <div class="mt-3">
                    <label for="letter_file" class="form-label">Upload Letter (PDF, DOC, DOCX, JPG, PNG)</label>
                    <input type="file" wire:model="letter_file" id="letter_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    @error('letter_file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-col w-1/3">
                    <label for="date_attendance" class="form-label">Date of Attendance</label>
                    <input type="date" wire:model="date_attendance" value="{{ $date_attendance }}" id="date_attendance" class="form-control">
                    @error('date_attendance') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="form-col w-1/3">
                    <label class="form-label">Session</label>
                    <div class="flex gap-3">
                        <button type="button" wire:click.prevent="$set('session_choice','am')" class="{{ ($session_choice ?? 'am') === 'am' ? 'h-10 px-4 rounded-md bg-slate-900 text-white hover:bg-slate-800 flex items-center justify-center' : 'h-10 px-4 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center justify-center' }}">AM</button>
                        <button type="button" wire:click.prevent="$set('session_choice','pm')" class="{{ ($session_choice ?? 'am') === 'pm' ? 'h-10 px-4 rounded-md bg-slate-900 text-white hover:bg-slate-800 flex items-center justify-center' : 'h-10 px-4 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center justify-center' }}">PM</button>
                        <button type="button" wire:click.prevent="$set('session_choice','both')" class="{{ ($session_choice ?? 'am') === 'both' ? 'h-10 px-4 rounded-md bg-slate-900 text-white hover:bg-slate-800 flex items-center justify-center' : 'h-10 px-4 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center justify-center' }}">Both</button>
                    </div>
                    @error('session_choice') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="form-col w-1/3 flex items-end">
                    <button type="submit" class="h-10 px-4 rounded-md bg-slate-900 text-white hover:bg-slate-800 flex items-center justify-center w-full">
                        <i class="fa fa-check mr-2" aria-hidden="true"></i>Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>