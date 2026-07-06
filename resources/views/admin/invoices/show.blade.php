@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">{{ $invoice->title }}</h1>
        <p class="muted">{{ $invoice->clientProfile->business_name }} • {{ $invoice->original_filename }}</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        @if(in_array($invoice->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true))
            <a class="btn secondary" target="_blank" href="{{ route('admin.invoices.preview', $invoice) }}">Preview</a>
        @endif
        <a class="btn secondary" href="{{ route('admin.invoices.download', $invoice) }}">Download</a>
        <form method="post" action="{{ route('admin.invoices.destroy', $invoice) }}" onsubmit="return confirm('Delete this invoice? This cannot be undone.')">
            @csrf
            @method('delete')
            <button class="btn danger" type="submit">Delete</button>
        </form>
    </div>
</div>
<section class="grid two">
    <div class="card">
        <h2>Review</h2>
        <form class="form" method="post" action="{{ route('admin.invoices.update', $invoice) }}">
            @csrf
            @method('patch')
            <div class="field">
                <label>Status</label>
                <select class="select" name="status">
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected($invoice->status === $status)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Reply / Comment</label>
                <textarea class="textarea" name="comment" placeholder="Add a note for the client or accountant file."></textarea>
            </div>
            <button class="btn" type="submit">Save Review</button>
        </form>
    </div>
    <div class="card">
        <h2>File details</h2>
        <div class="file-meta">
            <span>Status: <span class="badge {{ $invoice->status->value }}">{{ $invoice->status->label() }}</span></span>
            <span>Amount: {{ $invoice->amount ? $invoice->currency.' '.$invoice->amount : 'Not provided' }}</span>
            <span>Expense date: {{ $invoice->expense_date?->format('M d, Y') ?? 'Not provided' }}</span>
            <span>Original size: {{ number_format($invoice->original_size / 1024, 1) }} KB</span>
            <span>Compressed size: {{ $invoice->compressed_size ? number_format($invoice->compressed_size / 1024, 1).' KB' : 'Not compressed' }}</span>
            <span>Optimization: {{ ucfirst($invoice->optimization_status) }}{{ $invoice->optimization_notes ? ' - '.$invoice->optimization_notes : '' }}</span>
            <span>Disk: {{ strtoupper($invoice->storage_disk) }}</span>
            <span>Folder: {{ $invoice->stored_path }}</span>
        </div>
    </div>
</section>
<section class="card" style="margin-top:16px">
    <h2>Comments</h2>
    <div class="comments">
        @forelse($invoice->comments as $comment)
            <div class="comment"><strong>{{ $comment->user->name }}</strong><p>{{ $comment->body }}</p></div>
        @empty
            <p class="muted">No comments yet.</p>
        @endforelse
    </div>
</section>
@endsection
