@props(['messages' => null])

{{--
    Support two usage patterns:
    1) <x-input-error :messages="$errors->get('field')" />
    2) <x-input-error>{{ $message }}</x-input-error>
    If the caller passed a slot but not the `messages` prop, use the slot as a single message.
--}}
@php
    // If messages isn't provided but the component received slot content, use that.
    if (empty($messages) && trim($slot ?? '') !== '') {
        $messages = [trim($slot)];
    }
@endphp

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
