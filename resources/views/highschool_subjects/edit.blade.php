@extends('layouts.app')

@section('content')
<div class="px-8 py-6 pb-12">
      {{-- Use Livewire edit component for the form UI; keep scripts and layout here. --}}
      @livewire('highschool-subjects.edit', ['subject' => $subject])
</div>

    {{-- Alpine helper for subject form (title-case input and Livewire sync). Kept here so both create/edit parent blades share the same script. --}}
    @push('scripts')
    <script>
    (function(){
        function makeSubjectForm() {
            return {
                onNameInput(e) {
                    const v = (e.target.value || '').toString();
                    const t = this.titleCase(v);
                    clearTimeout(this._debName);
                    this._debName = setTimeout(() => {
                        this.syncLivewire('subject_name', t);
                    }, 220);
                },
                onCodeInput(e) {
                    const v = (e.target.value || '').toString();
                    clearTimeout(this._debCode);
                    this._debCode = setTimeout(() => {
                        this.syncLivewire('subject_code', v);
                    }, 220);
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
                initFromDom() {}
            };
        }
        if (window.Alpine && typeof Alpine.data === 'function') {
            try { Alpine.data('subjectForm', makeSubjectForm); } catch(e) {}
        } else { window.subjectForm = makeSubjectForm; }
    })();
    </script>
    @endpush

    </div>
  </div>
</div>
@endsection
