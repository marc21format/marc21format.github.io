<div class="profile-form-card">
    <h3 class="profile-card-title">Add Committee Membership for {{ $user->name }}</h3>
    <div x-data="{ positions: [], selectedCommittee: null, selectedPosition: null, filteredPositions() { if (! this.selectedCommittee) return this.positions; return this.positions.filter(p => (String(p.committee_ids||'').split(',').map(s => s ? Number(s) : null).filter(Boolean)).includes(Number(this.selectedCommittee))); } }"
        x-init="positions = JSON.parse($el.dataset.positions || '[]'); selectedCommittee = $el.dataset.prefill || null; if (selectedCommittee === 'null') selectedCommittee = null; selectedPosition = $el.dataset.selpos || null;"
        data-positions='@json($positions->toArray())'
        data-prefill='{{ $prefillCommittee ?? old('committee_id') ?? '' }}'
        data-selpos='{{ old('position_id') ?? '' }}'>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <form method="POST" action="{{ route('profile.volunteer.committee_members.store', ['user' => $user->id]) }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Committee</label>
            <select name="committee_id" class="form-select" x-model="selectedCommittee" @change="selectedPosition = null">
                <option value="">-- select committee --</option>
                @foreach($committees as $c)
                    <option value="{{ $c->committee_id }}" {{ (old('committee_id') == $c->committee_id || $prefillCommittee == $c->committee_id) ? 'selected' : '' }}>{{ $c->committee_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Position</label>
            <select name="position_id" class="form-select" x-model="selectedPosition">
                <option value="">-- select position --</option>
                <template x-for="p in filteredPositions()" :key="p.position_id">
                    <option :value="p.position_id" x-text="p.position_name" :selected="String(selectedPosition) === String(p.position_id)"></option>
                </template>
            </select>
        </div>
        <div class="form-actions mt-4">
            <button class="btn btn-primary" type="submit">Add</button>
            <a href="{{ route('profile.volunteer.show', ['user' => $user->id]) }}" class="small-link ms-3">Cancel</a>
        </div>
        </form>
    </div>
</div>
