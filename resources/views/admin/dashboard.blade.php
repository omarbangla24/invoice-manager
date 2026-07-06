@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Admin Dashboard</h1>
        <p class="muted">Client invoice intake, status, and review overview.</p>
    </div>
    <a class="btn" href="{{ route('admin.clients.create') }}">Add Client</a>
</div>
<section class="grid stats">
    <div class="card"><div class="muted">Clients</div><div class="stat">{{ $clientCount }}</div></div>
    <div class="card"><div class="muted">Invoices</div><div class="stat">{{ $invoiceCount }}</div></div>
    <div class="card"><div class="muted">Pending</div><div class="stat">{{ $pendingCount }}</div></div>
    <div class="card"><div class="muted">Done</div><div class="stat">{{ $doneCount }}</div></div>
</section>
<section class="grid" style="margin-top:16px">
    <div class="card">
        <h2>Client folders</h2>
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
    </div>
</section>
@endsection
