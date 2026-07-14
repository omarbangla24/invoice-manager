@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Notifications</h1>
        <p class="muted">Recent system updates.</p>
    </div>
    <form method="post" action="{{ route('notifications.read-all') }}">
        @csrf
        <button class="btn secondary" type="submit">Mark all as read</button>
    </form>
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
        <thead><tr><th>Title</th><th>Body</th><th>Date</th><th></th></tr></thead>
        <tbody>
        @forelse($notifications as $notification)
            <tr>
                <td>
                    @if($notification->url)
                        <a href="{{ $notification->url }}">{{ $notification->title }}</a>
                    @else
                        {{ $notification->title }}
                    @endif
                    @unless($notification->read_at)
                        <span class="badge pending" style="margin-left:6px">Unread</span>
                    @endunless
                </td>
                <td>{{ $notification->body }}</td>
                <td>{{ $notification->created_at->format('M d, Y h:i A') }}</td>
                <td>
                    @unless($notification->read_at)
                        <form method="post" action="{{ route('notifications.read', $notification->id) }}" style="display:inline">
                            @csrf
                            <button class="btn secondary" type="submit" style="padding:4px 10px;font-size:13px">Mark read</button>
                        </form>
                    @endunless
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">No notifications.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $notifications->links() }}</div>
</section>
@endsection
