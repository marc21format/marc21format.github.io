<tr>
    <td>{{ $record->school_name }}</td>
    <td>{{ optional($record->program)->name ?? '-' }}</td>
    <td>
        @if($record->degree_level) {{ $record->degree_level->name }} @endif
        @if($record->degree_type) - {{ $record->degree_type->name }} @endif
    </td>
    <td>{{ $record->year_graduated ?? '-' }}</td>
    <td class="text-right">
        @if(auth()->check() && (auth()->id() === $record->user_id || in_array(auth()->user()->role_id, [1,2,3])))
            <button wire:click="delete" class="btn btn-danger btn-sm">Delete</button>
        @endif
    </td>
</tr>
