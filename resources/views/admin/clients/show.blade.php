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
        <input class="input" name="q" value="{{ request('q') }}" placeholder="Search filename, supplier, ABN">
        <select class="select" name="status">
            <option value="">All statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <select class="select" name="source">
            <option value="">All sources</option>
            <option value="portal" @selected(request('source') === 'portal')>Portal</option>
            <option value="email" @selected(request('source') === 'email')>Email</option>
        </select>
        <input class="input" name="date_from" type="date" value="{{ request('date_from') }}" title="Uploaded from">
        <input class="input" name="date_to" type="date" value="{{ request('date_to') }}" title="Uploaded to">
        <input class="input" name="supplier" value="{{ request('supplier') }}" placeholder="Supplier">
        <input class="input" name="abn" value="{{ request('abn') }}" placeholder="ABN">
        <select class="select" name="category">
            <option value="">All categories</option>
            @foreach($categories as $category)
                <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <input class="input" name="invoice_date_from" type="date" value="{{ request('invoice_date_from') }}" title="Invoice date from">
        <input class="input" name="invoice_date_to" type="date" value="{{ request('invoice_date_to') }}" title="Invoice date to">
        <input class="input" name="due_date_from" type="date" value="{{ request('due_date_from') }}" title="Due date from">
        <input class="input" name="due_date_to" type="date" value="{{ request('due_date_to') }}" title="Due date to">
        <input class="input" name="amount_min" type="number" step="0.01" min="0" value="{{ request('amount_min') }}" placeholder="Amount min">
        <input class="input" name="amount_max" type="number" step="0.01" min="0" value="{{ request('amount_max') }}" placeholder="Amount max">
        <input class="input" name="gst_min" type="number" step="0.01" min="0" value="{{ request('gst_min') }}" placeholder="GST min">
        <input class="input" name="gst_max" type="number" step="0.01" min="0" value="{{ request('gst_max') }}" placeholder="GST max">
        <select class="select" name="per_page">
            @foreach([20, 50, 100] as $size)
                <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }} / page</option>
            @endforeach
        </select>
        <button class="btn" type="submit">Filter</button>
        <a class="btn secondary" href="{{ route('admin.clients.show', $client) }}">Reset</a>
    </form>
    @include('admin.invoices.partials.table', ['invoices' => $invoices])
    <div class="pagination-wrap">{{ $invoices->links() }}</div>
</section>
@endsection
