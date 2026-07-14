@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Add Job</h1>
        <p class="muted">Create a new job for the catalog.</p>
    </div>
</div>
@if($errors->any())
    <div class="alert err">{{ $errors->first() }}</div>
@endif
<section class="card">
    <form class="form" method="post" action="{{ route('admin.jobs.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="field"><label>Type of Service</label><input class="input" name="type_of_service" value="{{ old('type_of_service') }}" required></div>
        <div class="field"><label>Category</label><input class="input" name="category" value="{{ old('category') }}" placeholder="e.g. Tax, BAS, Advisory"></div>
        <div class="field"><label>Description</label><textarea class="textarea" name="description">{{ old('description') }}</textarea></div>
        <div class="settings-row">
            <div class="field"><label>QTY</label><input class="input" name="qty" type="number" min="1" value="{{ old('qty', 1) }}" required></div>
            <div class="field"><label>Fees (AUD)</label><input class="input" name="fees" type="number" min="0" step="0.01" value="{{ old('fees', '0.00') }}" required></div>
        </div>
        <div class="field">
            <label>Attachment <span class="muted">(optional — JPG, PNG, PDF, max 10MB)</span></label>
            <input class="input" name="attachment" type="file" accept=".jpg,.jpeg,.png,.pdf">
        </div>
        <button class="btn" type="submit">Create Job</button>
    </form>
</section>
@endsection
