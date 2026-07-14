@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">{{ $client->business_name }}</h1>
        <p class="muted">Upload expense receipts and track accountant review status.</p>
    </div>
    <a class="btn" href="{{ route('client.invoices.create') }}">Upload Invoice</a>
</div>

<h2 class="section-heading">Invoices</h2>
<section class="grid stats">
    <a class="card stat-link" href="{{ route('client.invoices.index', ['status' => 'pending']) }}"><div class="muted">Pending</div><div class="stat">{{ $pendingCount }}</div></a>
    <a class="card stat-link" href="{{ route('client.invoices.index', ['status' => 'done']) }}"><div class="muted">Done / Counted</div><div class="stat">{{ $doneCount }}</div></a>
    <a class="card stat-link" href="{{ route('client.invoices.index', ['status' => 'declined']) }}"><div class="muted">Declined</div><div class="stat">{{ $declinedCount }}</div></a>
    <a class="card stat-link" href="{{ route('client.invoices.index') }}"><div class="muted">Total</div><div class="stat">{{ $pendingCount + $doneCount + $declinedCount }}</div></a>
</section>

<h2 class="section-heading">Job Requests</h2>
<section class="grid stats">
    <a class="card stat-link" href="{{ route('client.job-requests.index') }}"><div class="muted">Total Requests</div><div class="stat">{{ $jobRequestCount }}</div></a>
    <a class="card stat-link" href="{{ route('client.job-requests.index', ['status' => 'pending']) }}"><div class="muted">Pending</div><div class="stat">{{ $jobRequestPendingCount }}</div></a>
    <a class="card stat-link" href="{{ route('client.job-requests.index', ['status' => 'in_progress']) }}"><div class="muted">In Progress</div><div class="stat">{{ $jobRequestInProgressCount }}</div></a>
    <a class="card stat-link" href="{{ route('client.job-requests.index', ['status' => 'completed']) }}"><div class="muted">Completed</div><div class="stat">{{ $jobRequestCompletedCount }}</div></a>
</section>

<section class="card" style="margin-top:16px">
    <h2>Recent invoices</h2>
    @include('client.invoices.partials.table', ['invoices' => $recentInvoices])
</section>
@endsection
