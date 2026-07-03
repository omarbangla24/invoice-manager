@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Audit Logs</h1>
        <p class="muted">Important system actions.</p>
    </div>
</div>
<form class="filterbar" method="get">
    <input class="input" name="q" value="{{ request('q') }}" placeholder="Search action, subject, metadata">
    <select class="select" name="action">
        <option value="">All actions</option>
        @foreach($actions as $action)
            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
        @endforeach
    </select>
    <input class="input" name="date_from" type="date" value="{{ request('date_from') }}">
    <input class="input" name="date_to" type="date" value="{{ request('date_to') }}">
    <select class="select" name="per_page">
        @foreach([30, 50, 100] as $size)
            <option value="{{ $size }}" @selected((int) request('per_page', 30) === $size)>{{ $size }} / page</option>
        @endforeach
    </select>
    <button class="btn" type="submit">Filter</button>
    <a class="btn secondary" href="{{ route('admin.audit-logs.index') }}">Reset</a>
</form>
<section class="card">
    <table class="table">
        <thead><tr><th>Action</th><th>User</th><th>Subject</th><th>Metadata</th><th>Date</th></tr></thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td>{{ $log->action }}</td>
                <td>{{ $log->user_id ?: 'System' }}</td>
                <td>{{ class_basename($log->subject_type ?: '') }} #{{ $log->subject_id }}</td>
                <td><code>{{ json_encode($log->metadata) }}</code></td>
                <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">No audit logs.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $logs->links() }}</div>
</section>
@endsection
