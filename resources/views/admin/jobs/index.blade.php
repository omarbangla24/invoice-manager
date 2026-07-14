@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Jobs</h1>
        <p class="muted">Manage the job catalog available to all clients.</p>
    </div>
    <a class="btn" href="{{ route('admin.jobs.create') }}">Add Job</a>
</div>
<form class="filterbar" method="get">
    <input class="input" name="q" value="{{ request('q') }}" placeholder="Search jobs">
    <select class="select" name="category">
        <option value="">All categories</option>
        @foreach($categories as $category)
            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
        @endforeach
    </select>
    <select class="select" name="per_page">
        @foreach([20, 50, 100] as $size)
            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }} / page</option>
        @endforeach
    </select>
    <button class="btn" type="submit">Search</button>
    <a class="btn secondary" href="{{ route('admin.jobs.index') }}">Reset</a>
</form>
<div class="card">
    <table class="table">
        <thead><tr><th>Type of Service</th><th>Category</th><th>Description</th><th>QTY</th><th>Fees</th><th>Attachment</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($jobs as $job)
            <tr>
                <td>{{ $job->type_of_service }}</td>
                <td>{{ $job->category ?: '—' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($job->description, 60) }}</td>
                <td>{{ $job->qty }}</td>
                <td>AUD {{ number_format($job->fees, 2) }}</td>
                <td>
                    @if($job->attachment_path)
                        <a href="{{ route('admin.jobs.attachment', $job) }}" title="{{ $job->attachment_filename }}">
                            @if(str_starts_with($job->attachment_mime ?? '', 'image/'))
                                <span class="badge in_progress" style="font-size:11px">Image</span>
                            @else
                                <span class="badge pending" style="font-size:11px">PDF</span>
                            @endif
                        </a>
                    @else
                        —
                    @endif
                </td>
                <td><span class="badge {{ $job->is_active ? 'done' : 'declined' }}">{{ $job->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>
                    <a class="btn secondary" href="{{ route('admin.jobs.edit', $job) }}" style="padding:4px 10px;font-size:13px">Edit</a>
                    <form method="post" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('Delete this job?')" style="display:inline">
                        @csrf
                        @method('delete')
                        <button class="btn danger" type="submit" style="padding:4px 10px;font-size:13px">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted">No jobs yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $jobs->links() }}</div>
</div>
@endsection
