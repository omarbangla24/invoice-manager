@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Clients</h1>
        <p class="muted">Separate logins and folders for each business owner.</p>
    </div>
    <a class="btn" href="{{ route('admin.clients.create') }}">Add Client</a>
</div>
<form class="filterbar" method="get">
    <input class="input" name="q" value="{{ request('q') }}" placeholder="Search business, contact, email, webmail">
    <select class="select" name="per_page">
        @foreach([15, 25, 50, 100] as $size)
            <option value="{{ $size }}" @selected((int) request('per_page', 15) === $size)>{{ $size }} / page</option>
        @endforeach
    </select>
    <button class="btn" type="submit">Search</button>
    <a class="btn secondary" href="{{ route('admin.clients.index') }}">Reset</a>
</form>
<div class="card">
    <table class="table">
        <thead><tr><th>Business</th><th>Email</th><th>Webmail</th><th>Folder</th><th>Invoices</th><th></th></tr></thead>
        <tbody>
        @forelse($clients as $client)
            <tr>
                <td><a href="{{ route('admin.clients.show', $client) }}">{{ $client->business_name }}</a></td>
                <td>{{ $client->user->email }}</td>
                <td>{{ $client->webmail_address ?: 'Not set' }}</td>
                <td>{{ $client->storage_folder }}</td>
                <td>{{ $client->invoices_count }}</td>
                <td>
                    @if($client->invoices_count === 0)
                        <form method="post" action="{{ route('admin.clients.destroy', $client) }}" onsubmit="return confirm('Delete this client?')">
                            @csrf
                            @method('delete')
                            <button class="btn danger" type="submit" style="padding:4px 10px;font-size:13px">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">No clients yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $clients->links() }}</div>
</div>
@endsection
