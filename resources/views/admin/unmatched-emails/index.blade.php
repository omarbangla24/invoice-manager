@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Unmatched Email</h1>
        <p class="muted">Email attachments that could not be matched to a client yet.</p>
    </div>
</div>
<form class="filterbar" method="get">
    <input class="input" name="q" value="{{ request('q') }}" placeholder="Search from, to, subject">
    <input class="input" name="date_from" type="date" value="{{ request('date_from') }}">
    <input class="input" name="date_to" type="date" value="{{ request('date_to') }}">
    <select class="select" name="per_page">
        @foreach([20, 50, 100] as $size)
            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }} / page</option>
        @endforeach
    </select>
    <button class="btn" type="submit">Filter</button>
    <a class="btn secondary" href="{{ route('admin.unmatched-emails.index') }}">Reset</a>
</form>
<div class="card">
    <table class="table">
        <thead><tr><th>From</th><th>To</th><th>Subject</th><th>Files</th><th>Received</th></tr></thead>
        <tbody>
        @forelse($emails as $email)
            <tr>
                <td><a href="{{ route('admin.unmatched-emails.show', $email) }}">{{ $email->from_email }}</a></td>
                <td>{{ $email->to_email ?: 'Not provided' }}</td>
                <td>{{ $email->subject ?: 'No subject' }}</td>
                <td>{{ $email->attachments_count }}</td>
                <td>{{ $email->created_at->format('M d, Y h:i A') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">No unmatched emails.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $emails->links() }}</div>
</div>
@endsection
