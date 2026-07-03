@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">{{ $invoice->title }}</h1>
        <p class="muted">{{ $invoice->original_filename }}</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        @if(in_array($invoice->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true))
            <a class="btn secondary" target="_blank" href="{{ route('client.invoices.preview', $invoice) }}">Preview</a>
        @endif
        <a class="btn secondary" href="{{ route('client.invoices.download', $invoice) }}">Download</a>
    </div>
</div>
<section class="grid two">
    <div class="card">
        <h2>Status</h2>
        <p><span class="badge {{ $invoice->status->value }}">{{ $invoice->status->label() }}</span></p>
        <div class="file-meta">
            <span>Amount: {{ $invoice->amount ? $invoice->currency.' '.$invoice->amount : 'Not provided' }}</span>
            <span>Expense date: {{ $invoice->expense_date?->format('M d, Y') ?? 'Not provided' }}</span>
            <span>Uploaded: {{ $invoice->created_at->format('M d, Y h:i A') }}</span>
        </div>
    </div>
    <div class="card">
        <h2>Accountant replies</h2>
        <div class="comments">
            @forelse($invoice->comments as $comment)
                <div class="comment"><strong>{{ $comment->user->name }}</strong><p>{{ $comment->body }}</p></div>
            @empty
                <p class="muted">No reply yet.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
