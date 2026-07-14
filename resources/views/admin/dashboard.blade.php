@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Admin Dashboard</h1>
        <p class="muted">Client invoice intake, status, and review overview.</p>
    </div>
</div>

<h2 class="section-heading">Invoices & Clients</h2>
<section class="grid stats">
    <a class="card stat-link" href="{{ route('admin.clients.index') }}"><div class="muted">Clients</div><div class="stat">{{ $clientCount }}</div></a>
    <a class="card stat-link" href="{{ route('admin.invoices.index') }}"><div class="muted">Invoices</div><div class="stat">{{ $invoiceCount }}</div></a>
    <a class="card stat-link" href="{{ route('admin.invoices.index', ['status' => 'pending']) }}"><div class="muted">Pending</div><div class="stat">{{ $pendingCount }}</div></a>
    <a class="card stat-link" href="{{ route('admin.invoices.index', ['status' => 'done']) }}"><div class="muted">Done</div><div class="stat">{{ $doneCount }}</div></a>
</section>

<h2 class="section-heading">Job Requests</h2>
<section class="grid stats">
    <a class="card stat-link" href="{{ route('admin.job-requests.index') }}"><div class="muted">Total Requests</div><div class="stat">{{ $jobRequestCount }}</div></a>
    <a class="card stat-link" href="{{ route('admin.job-requests.index', ['status' => 'pending']) }}"><div class="muted">Pending</div><div class="stat">{{ $jobRequestPendingCount }}</div></a>
    <a class="card stat-link" href="{{ route('admin.job-requests.index', ['status' => 'in_progress']) }}"><div class="muted">In Progress</div><div class="stat">{{ $jobRequestInProgressCount }}</div></a>
    <a class="card stat-link" href="{{ route('admin.job-requests.index', ['status' => 'completed']) }}"><div class="muted">Completed</div><div class="stat">{{ $jobRequestCompletedCount }}</div></a>
</section>

<section class="card" style="margin-top:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
        <h2 style="margin:0">Client folders</h2>
        <a class="btn secondary" href="{{ route('admin.clients.index') }}" style="padding:6px 12px;font-size:13px">View all</a>
    </div>
    <table class="table">
        <thead><tr><th>Client</th><th>Folder</th><th>Pending</th></tr></thead>
        <tbody>
        @foreach($clients as $client)
            <tr>
                <td><a href="{{ route('admin.clients.show', $client) }}">{{ $client->business_name }}</a></td>
                <td>{{ $client->storage_folder }}</td>
                <td>{{ $client->pending_invoices_count }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</section>
@endsection
