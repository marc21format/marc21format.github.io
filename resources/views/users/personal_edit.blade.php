@extends('layouts.app')

@section('content')
<div class="container">
        <div class="card-body">
        @livewire('profile.personal-information-edit', ['userId' => $user->id])
        </div>
</div>
    
    <script>
        (function(){
        function makePersonalForm() {
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
            try { Alpine.data('personalForm', makePersonalForm); } catch(e) {}
        } else {
            window.personalForm = makePersonalForm;
        }
        })();
    </script>

    <script>
        // Bridge: make selects inside Livewire address component more responsive by
        // emitting a Livewire event when the native select changes. This helps in
        // cases where Alpine/Livewire load order causes selects to not update until
        // a manual reload.
        (function(){
            function emitSet(field, value){
                try{
                    if (window.Livewire && typeof window.Livewire.emit === 'function') {
                        window.Livewire.emit('setAddressField', field, value);
                    } else if (window.livewire && typeof window.livewire.emit === 'function') {
                        window.livewire.emit('setAddressField', field, value);
                    }
                }catch(e){ console.debug('emitSet error', e); }
            }

            function attach() {
                // province select
                var prov = document.querySelector('select[wire\\:model="province_id"], select[wire:model="province_id"]');
                if (prov) prov.addEventListener('change', function(e){ emitSet('province_id', e.target.value); });

                // city select
                var city = document.querySelector('select[wire\\:model="city_id"], select[wire:model="city_id"]');
                if (city) city.addEventListener('change', function(e){ emitSet('city_id', e.target.value); });

                // barangay select
                var brgy = document.querySelector('select[wire\\:model="barangay_id"], select[wire:model="barangay_id"]');
                if (brgy) brgy.addEventListener('change', function(e){ emitSet('barangay_id', e.target.value); });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', attach);
            } else {
                attach();
            }

            // Re-attach when Livewire updates DOM (for dynamic re-rendering)
            try {
                if (window.Livewire && typeof window.Livewire.on === 'function') {
                    window.Livewire.hook('afterDomUpdate', () => { attach(); });
                } else if (window.livewire && typeof window.livewire.hook === 'function') {
                    window.livewire.hook('afterDomUpdate', () => { attach(); });
                }
            } catch(e) { /* ignore */ }
        })();
    </script>
@endsection
