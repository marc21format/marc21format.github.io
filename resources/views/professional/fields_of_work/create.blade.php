@extends('layouts.app')

@section('content')
<div class="profile-component-card">
	<div class="profile-card-header">
		<div>
			<p class="profile-card-title">Add Field of Work</p>
		</div>
		<div class="profile-card-actions">
			<button type="submit" form="form" class="gear-button text-slate-800" title="Add">
				<i class="fa fa-check" aria-hidden="true"></i>
			</button>
			<a href="{{ route('fields_of_work.index') }}" class="gear-button text-slate-800" title="Cancel">
				<i class="fa fa-times" aria-hidden="true"></i>
			</a>
		</div>
	</div>

	<div class="border-t border-slate-200 pt-4">
		<form id="form" method="POST" action="{{ route('fields_of_work.store') }}">
			@csrf

			<div class="form-group">
				<label for="name" class="form-label">Name</label>
				<input id="name" name="name" class="form-input" value="{{ old('name') }}" autocomplete="off" />
			</div>

			{{-- actions moved to header --}}
		</form>
	</div>
</div>
@endsection
