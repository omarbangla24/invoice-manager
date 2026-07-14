@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Edit Job</h1>
        <p class="muted">Update job details.</p>
    </div>
</div>
@if($errors->any())
    <div class="alert err">{{ $errors->first() }}</div>
@endif
<section class="card">
    <form class="form" method="post" action="{{ route('admin.jobs.update', $job) }}" enctype="multipart/form-data">
        @csrf
        @method('patch')
        <div class="field"><label>Type of Service</label><input class="input" name="type_of_service" value="{{ old('type_of_service', $job->type_of_service) }}" required></div>
        <div class="field"><label>Category</label><input class="input" name="category" value="{{ old('category', $job->category) }}" placeholder="e.g. Tax, BAS, Advisory"></div>
        <div class="field"><label>Description</label><textarea class="textarea" name="description">{{ old('description', $job->description) }}</textarea></div>
        <div class="settings-row">
            <div class="field"><label>QTY</label><input class="input" name="qty" type="number" min="1" value="{{ old('qty', $job->qty) }}" required></div>
            <div class="field"><label>Fees (AUD)</label><input class="input" name="fees" type="number" min="0" step="0.01" value="{{ old('fees', $job->fees) }}" required></div>
        </div>
        <div class="field">
            <label>Attachment <span class="muted">(optional — JPG, PNG, PDF, max 10MB)</span></label>
            @if($job->attachment_path)
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
                    <a class="btn secondary" href="{{ route('admin.jobs.attachment', $job) }}" style="padding:6px 12px;font-size:13px">
                        {{ $job->attachment_filename }}
                    </a>
                    <label style="font-weight:400;font-size:13px;display:flex;align-items:center;gap:6px;cursor:pointer">
                        <input type="checkbox" name="remove_attachment" value="1"> Remove current
                    </label>
                </div>
            @endif
            <input class="input" name="attachment" type="file" accept=".jpg,.jpeg,.png,.pdf">
        </div>
        <div class="field">
            <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $job->is_active))> Active</label>
        </div>
        <button class="btn" type="submit">Update Job</button>
    </form>
</section>
@endsection
