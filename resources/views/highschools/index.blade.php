@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card mt-4 form-card form-card--wide">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Highschools</h2>
        <a href="{{ route('highschools.create') }}" class="btn btn-primary">Add Highschool</a>
      </div>

      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Abbreviation</th>
            <th>Type</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($highschools as $h)
            <tr>
              <td>
                <a href="{{ route('highschools.edit', $h->highschool_id) }}">{{ $h->highschool_name }}</a>
              </td>
              <td>{{ $h->abbreviation }}</td>
              <td>
                {{ $h->type }}
              </td>
              <td class="text-end">
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isExecutive()))
                  <form method="POST" action="{{ route('highschools.destroy', $h->highschool_id) }}" class="d-inline-block" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    {{-- Larger × button on the far-right; confirmation handled by onclick returning confirm() --}}
                    <button type="submit" class="btn btn-link text-danger fs-4 ms-3" onclick="return confirm('Delete this highschool?')" title="Delete">&times;</button>
                  </form>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      {{ $highschools->links() }}
    </div>
  </div>
</div>
@endsection
