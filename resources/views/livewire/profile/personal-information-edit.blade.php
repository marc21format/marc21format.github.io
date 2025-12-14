@php
    $user = $user ?? ($this->user ?? auth()->user());
    $p = $user->userProfile ?? new \App\Models\UserProfile();

    $targetId = $user->id ?? auth()->id();
    $target = \App\Models\User::find($targetId);
    $cancelRoute = ( $target && in_array($target->role_id, [1,2,3], true) ) ? route('profile.volunteer.show', ['user' => $targetId]) : route('profile.student.show', ['user' => $targetId]);
@endphp

<div x-data="personalForm()" x-init="initFromDom()" class="profile-component-card profile-form-card max-w-4xl mx-auto">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Edit Personal Information</p>
            <p class="profile-card-subtitle">Of {{ $user->name ?? ($user->email ?? 'User') }}</p>
        </div>
        <div class="profile-card-actions">
            <button type="button" class="gear-button" onclick="Livewire.emit('saveAddressFromParent')" wire:click.prevent="saveProfile" title="Save">
                <i class="fa fa-check" aria-hidden="true"></i>
            </button>
            <a href="{{ $cancelRoute }}" class="gear-button" title="Cancel">
                <i class="fa fa-times" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        @if(session('success'))
            <div class="mb-3 text-green-700">{{ session('success') }}</div>
        @endif

        <div class="grid gap-6 grid-cols-1 md:grid-cols-2">
            <div>
                <div class="profile-form-card">
                    <h3 class="mb-3">Identification Details</h3>
                    <form wire:submit.prevent="saveProfile" class="space-y-4">
                        @csrf

                        <div class="form-group">
                            <label class="form-label">First name</label>
                            <input id="f_name" type="text" wire:model.defer="f_name" x-on:input="onNameInput($event, 'f_name')" class="form-input">
                            @error('f_name') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Middle name</label>
                            <input id="m_name" type="text" wire:model.defer="m_name" x-on:input="onNameInput($event, 'm_name')" class="form-input">
                            @error('m_name') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Surname</label>
                            <input id="s_name" type="text" wire:model.defer="s_name" x-on:input="onNameInput($event, 's_name')" class="form-input">
                            @error('s_name') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Lived name</label>
                            <input id="lived_name" type="text" wire:model.defer="lived_name" x-on:input="onNameInput($event, 'lived_name')" class="form-input">
                            @error('lived_name') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Generational suffix</label>
                            <input id="generational_suffix" type="text" wire:model.defer="generational_suffix" x-on:input="onGenericInput($event, 'generational_suffix')" class="form-input">
                            @error('generational_suffix') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone number</label>
                            <input id="phone_number" type="text" wire:model.defer="phone_number" x-on:input="onGenericInput($event, 'phone_number')" class="form-input">
                            @error('phone_number') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Birthday</label>
                            <input id="birthday" type="date" wire:model.defer="birthday" x-on:change="onGenericInput($event, 'birthday')" class="form-input">
                            @error('birthday') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Sex</label>
                            <select id="sex" wire:model.defer="sex" x-on:change="onGenericInput($event, 'sex')" class="form-select">
                                <option value="">--</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            @error('sex') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        {{-- actions handled in header --}}
                    </form>
                </div>
            </div>

            <div>
                @livewire('address.edit', ['userId' => $user->id, 'hideActions' => true])
            </div>
        </div>
    </div>
</div>

    <script>
        // Robust Address select bridge: emit Livewire event when native selects change
        // and reattach after Livewire updates. This lives inside the personal-info
        // Livewire view so it's present whenever the personal-information-edit
        // component is rendered.
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

            function attachOnce(el, ev, fn){
                if (!el) return;
                // prevent duplicate handlers on repeated attaches
                if (el.__addressBridgeAttached) return;
                el.addEventListener(ev, fn);
                el.__addressBridgeAttached = true;
            }

            function attach(){
                var prov = document.querySelector('select[wire\\:model="province_id"], select[wire:model="province_id"]');
                if (prov) attachOnce(prov, 'change', function(e){ emitSet('province_id', e.target.value); });

                var city = document.querySelector('select[wire\\:model="city_id"], select[wire:model="city_id"]');
                if (city) attachOnce(city, 'change', function(e){ emitSet('city_id', e.target.value); });

                var brgy = document.querySelector('select[wire\\:model="barangay_id"], select[wire:model="barangay_id"]');
                if (brgy) attachOnce(brgy, 'change', function(e){ emitSet('barangay_id', e.target.value); });
            }

            function setup(){
                attach();

                // When Livewire loads, hook into its message processed lifecycle to
                // re-attach after DOM updates (works for recent Livewire versions).
                document.addEventListener('livewire:load', function(){
                    try{
                        if (window.Livewire && typeof window.Livewire.hook === 'function') {
                            window.Livewire.hook('message.processed', function() { attach(); });
                        } else if (window.livewire && typeof window.livewire.hook === 'function') {
                            window.livewire.hook('afterDomUpdate', function() { attach(); });
                        } else {
                            // fallback event
                            document.addEventListener('livewire:afterDomUpdate', attach);
                        }
                    }catch(e){ /* ignore */ }
                });

                // As a safety-net, also observe DOM mutations and try to attach.
                try{
                    var mo = new MutationObserver(function(){ attach(); });
                    mo.observe(document.body, { childList: true, subtree: true });
                }catch(e){ /* MutationObserver not available? ignore */ }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', setup);
            } else {
                setup();
            }
        })();
    </script>
</div>
