@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">My Invoices</h1>
        <p class="muted">Every file here belongs only to your client folder.</p>
    </div>
    <a class="btn" href="{{ route('client.invoices.create') }}">Upload Invoice</a>
</div>
<form class="filterbar" method="get">
    <input class="input" name="q" value="{{ request('q') }}" placeholder="Search title, filename">
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
    <input class="input" name="date_from" type="date" value="{{ request('date_from') }}">
    <input class="input" name="date_to" type="date" value="{{ request('date_to') }}">
    <select class="select" name="per_page">
        @foreach([20, 50, 100] as $size)
            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }} / page</option>
        @endforeach
    </select>
    <button class="btn" type="submit">Filter</button>
    <a class="btn secondary" href="{{ route('client.invoices.index') }}">Reset</a>
</form>
<div class="card">
    @include('client.invoices.partials.table', ['invoices' => $invoices])
    <div class="pagination-wrap">{{ $invoices->links() }}</div>
</div>
@endsection
