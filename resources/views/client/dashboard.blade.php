@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">{{ $client->business_name }}</h1>
        <p class="muted">Upload expense receipts and track accountant review status.</p>
    </div>
    <a class="btn" href="{{ route('client.invoices.create') }}">Upload Invoice</a>
</div>
<section class="grid stats">
    <div class="card"><div class="muted">Pending</div><div class="stat">{{ $pendingCount }}</div></div>
    <div class="card"><div class="muted">Done / Counted</div><div class="stat">{{ $doneCount }}</div></div>
    <div class="card"><div class="muted">Declined</div><div class="stat">{{ $declinedCount }}</div></div>
    <div class="card"><div class="muted">Total</div><div class="stat">{{ $pendingCount + $doneCount + $declinedCount }}</div></div>
</section>
<section class="card" style="margin-top:16px">
    <h2>Recent invoices</h2>
    @include('client.invoices.partials.table', ['invoices' => $recentInvoices])
</section>
@endsection
