@extends('layouts.app')

@section('content')
<div class="py-6">
    {{-- Let the Livewire component provide its own card/layout so styling matches other edit forms --}}
    @livewire('profile.account-information-edit', ['userId' => $user->id])
</div>

@endsection

@push('scripts')
<script>
    (function(){
    function makeAccountForm() {
        return {
        // Title-case name-like inputs; debounce and sync to Livewire without overwriting during typing
        onNameInput(e, field) {
            const v = (e.target.value || '').toString();
            const t = this.titleCase(v);
            clearTimeout(this._debName);
            this._debName = setTimeout(() => {
            this.syncLivewire(field, t);
            }, 900);
        },
        onGenericInput(e, field) {
            const v = (e.target.value || '').toString();
            clearTimeout(this._debGeneric);
            this._debGeneric = setTimeout(() => {
            this.syncLivewire(field, v);
            }, 900);
        },
        titleCase(s) {
            if (!s) return '';
            return s.split(/\s+/).filter(Boolean).map(word => {
            return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
            }).join(' ');
        },
        syncLivewire(field, value) {
            if (this.$wire && typeof this.$wire.set === 'function') {
            try {
                if (typeof field === 'string' && field.length > 0) {
                    this.$wire.set(field, value);
                }
            } catch(e) {}
            }
        },
        initFromDom() {},
        };
    }

    if (window.Alpine && typeof Alpine.data === 'function') {
        try { Alpine.data('accountForm', makeAccountForm); } catch(e) {}
    } else {
        window.accountForm = makeAccountForm;
    }
    })();
</script>
@endpush
