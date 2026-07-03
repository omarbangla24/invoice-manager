@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Notifications</h1>
        <p class="muted">Recent system updates.</p>
    </div>
</div>
<form class="filterbar" method="get">
    <input class="input" name="q" value="{{ request('q') }}" placeholder="Search notifications">
    <select class="select" name="status">
        <option value="">All</option>
        <option value="unread" @selected(request('status') === 'unread')>Unread</option>
        <option value="read" @selected(request('status') === 'read')>Read</option>
    </select>
    <select class="select" name="per_page">
        @foreach([20, 50, 100] as $size)
            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }} / page</option>
        @endforeach
    </select>
    <button class="btn" type="submit">Filter</button>
    <a class="btn secondary" href="{{ route('notifications.index') }}">Reset</a>
</form>
<section class="card">
    <table class="table">
        <thead><tr><th>Title</th><th>Body</th><th>Date</th></tr></thead>
        <tbody>
        @forelse($notifications as $notification)
            <tr>
                <td>
                    @if($notification->url)
                        <a href="{{ $notification->url }}">{{ $notification->title }}</a>
                    @else
                        {{ $notification->title }}
                    @endif
                </td>
                <td>{{ $notification->body }}</td>
                <td>{{ $notification->created_at->format('M d, Y h:i A') }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">No notifications.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $notifications->links() }}</div>
</section>
@endsection
