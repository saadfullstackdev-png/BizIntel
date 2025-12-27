@extends('layouts.app')
@section('title', 'Create Goldern Ticket Status')

@section('content')
<h2>Create Goldern Ticket Status</h2>

<form action="{{ route('admin.buisness-statuses.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
        
        {{-- @error('name')
            <span class="text-danger">{{ $message }}</span>
        @enderrorphp --}}
    </div>

    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" name="active" value="1" class="custom-control-input" id="active" {{ old('active') ? 'checked' : '' }}>
            <label class="custom-control-label" for="active">Active</label>
        </div>
    </div>
    <button type="submit" class="btn btn-success">Save</button>
    <a href="{{ route('admin.buisness-statuses.index') }}" class="btn btn-secondary">Back</a>
</form>
@endsection