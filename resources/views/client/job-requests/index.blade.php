@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">My Job Requests</h1>
        <p class="muted">Track your submitted service requests.</p>
    </div>
    <a class="btn" href="{{ route('client.job-requests.create') }}">New Job Request</a>
</div>
<form class="filterbar" method="get">
    <input class="input" name="q" value="{{ request('q') }}" placeholder="Search remarks">
    <select class="select" name="status">
        <option value="">All statuses</option>
        @foreach($statuses as $status)
            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
    <input class="input" name="date_from" type="date" value="{{ request('date_from') }}" title="Submitted from">
    <input class="input" name="date_to" type="date" value="{{ request('date_to') }}" title="Submitted to">
    <select class="select" name="per_page">
        @foreach([20, 50, 100] as $size)
            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }} / page</option>
        @endforeach
    </select>
    <button class="btn" type="submit">Filter</button>
    <a class="btn secondary" href="{{ route('client.job-requests.index') }}">Reset</a>
</form>
<div class="card">
    <table class="table">
        <thead><tr><th>ID</th><th>Services</th><th>Status</th><th>Remarks</th><th>Submitted</th><th></th></tr></thead>
        <tbody>
        @forelse($jobRequests as $jr)
            <tr>
                <td>#{{ $jr->id }}</td>
                <td>{{ $jr->items_count }}</td>
                <td><span class="badge {{ $jr->status->value }}">{{ $jr->status->label() }}</span></td>
                <td>{{ \Illuminate\Support\Str::limit($jr->remarks, 40) }}</td>
                <td>{{ $jr->created_at->format('M d, Y') }}</td>
                <td><a class="btn secondary" href="{{ route('client.job-requests.show', $jr) }}" style="padding:4px 10px;font-size:13px">View</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">No job requests yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $jobRequests->links() }}</div>
</div>
@endsection
