@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">{{ $client->business_name }}</h1>
        <p class="muted">{{ $client->user->email }} • Folder: {{ $client->storage_folder }}</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a class="btn secondary" href="{{ route('admin.clients.index') }}">All Clients</a>
        <a class="btn" href="{{ route('admin.clients.edit', $client) }}">Edit</a>
        @if($client->invoices()->count() === 0)
            <form method="post" action="{{ route('admin.clients.destroy', $client) }}" onsubmit="return confirm('Delete this client?')">
                @csrf
                @method('delete')
                <button class="btn danger" type="submit">Delete</button>
            </form>
        @endif
    </div>
</div>
<section class="grid two">
    <div class="card">
        <h2>Profile</h2>
        <div class="file-meta">
            <span>Contact: {{ $client->contact_name ?: 'Not set' }}</span>
            <span>Phone: {{ $client->phone ?: 'Not set' }}</span>
            <span>Tax ID: {{ $client->tax_identifier ?: 'Not set' }}</span>
            <span>Webmail: {{ $client->webmail_address ?: 'Not set' }}</span>
            <span>Details: {{ $client->details ?: 'Not set' }}</span>
        </div>
    </div>
    <div class="card">
        <h2>Storage layout</h2>
        <div class="file-meta">
            <span>Root: clients/{{ $client->storage_folder }}</span>
            <span>Original files: clients/{{ $client->storage_folder }}/YYYY/MM-Month/original</span>
            <span>Compressed files: clients/{{ $client->storage_folder }}/YYYY/MM-Month/compressed</span>
        </div>
    </div>
</section>
<section class="card" style="margin-top:16px">
    <h2>Invoices</h2>
    <form class="filterbar" method="get">
        <input class="input" name="q" value="{{ request('q') }}" placeholder="Search invoice title">
        <select class="select" name="status">
            <option value="">All statuses</option>
            @foreach(\App\Enums\InvoiceStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <select class="select" name="per_page">
            @foreach([15, 25, 50, 100] as $size)
                <option value="{{ $size }}" @selected((int) request('per_page', 15) === $size)>{{ $size }} / page</option>
            @endforeach
        </select>
        <button class="btn" type="submit">Filter</button>
        <a class="btn secondary" href="{{ route('admin.clients.show', $client) }}">Reset</a>
    </form>
    @include('admin.invoices.partials.table', ['invoices' => $invoices])
    <div class="pagination-wrap">{{ $invoices->links() }}</div>
</section>
@endsection
