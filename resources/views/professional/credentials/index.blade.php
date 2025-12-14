@extends('layouts.app')

@section('content')
<div class="p-4 bg-white rounded">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-medium">Professional Credentials</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="px-4 py-2">User</th>
                    <th class="px-4 py-2">Field</th>
                    <th class="px-4 py-2">Prefix</th>
                    <th class="px-4 py-2">Suffix</th>
                    <th class="px-4 py-2">Issued On</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($creds as $c)
                    <tr>
                        <td class="border px-4 py-2">{{ optional($c->user)->name ?? '—' }}</td>
                        <td class="border px-4 py-2">{{ optional($c->fieldOfWork)->name ?? '—' }}</td>
                        <td class="border px-4 py-2">{{ optional($c->prefix)->abbreviation ?? optional($c->prefix)->title ?? '—' }}</td>
                        <td class="border px-4 py-2">{{ optional($c->suffix)->abbreviation ?? optional($c->suffix)->title ?? '—' }}</td>
                        <td class="border px-4 py-2">{{ $c->issued_on ? (is_string($c->issued_on) ? $c->issued_on : $c->issued_on->format('Y')) : '—' }}</td>
                        <td class="border px-4 py-2 text-right">
                            <a href="{{ route('profile.professional_credentials.edit', ['user'=>$c->user->id,'credential'=>$c->credential_id]) }}" class="btn">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $creds->links() }}</div>
</div>
@endsection
