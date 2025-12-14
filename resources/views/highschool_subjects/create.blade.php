@extends('layouts.app')

@section('content')
  <div class="px-8 py-6 pb-12">
    @livewire('highschool-subjects.create', ['userId' => $userId ?? null])
  </div>

	<script>
	(function(){
		function makeSubjectForm() {
			return {
				// Called when name input changes; compute title-case but DO NOT overwrite
				// the DOM value immediately (that breaks typing/spacebar). Debounce
				// and sync the title-cased value to Livewire instead.
				onNameInput(e) {
					const v = (e.target.value || '').toString();
					const t = this.titleCase(v);
					clearTimeout(this._debName);
					this._debName = setTimeout(() => {
						this.syncLivewire('subject_name', t);
					}, 1000);
				},
				onCodeInput(e) {
					const v = (e.target.value || '').toString();
					clearTimeout(this._debCode);
					this._debCode = setTimeout(() => {
						this.syncLivewire('subject_code', v);
					}, 1000);
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
						} catch(e) { /* ignore */ }
					}
				},
				initFromDom() {
					// No-op placeholder if initial hydration needed in future
				}
			};
		}

		if (window.Alpine && typeof Alpine.data === 'function') {
			try { Alpine.data('subjectForm', makeSubjectForm); } catch(e) {}
		} else {
			// expose globally in case Alpine is loaded later or used without Alpine
			window.subjectForm = makeSubjectForm;
		}
	})();
	</script>

@endsection
