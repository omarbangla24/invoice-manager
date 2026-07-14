@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1" title="{{ $invoice->original_filename }}">{{ \Illuminate\Support\Str::limit($invoice->original_filename, 48) }}</h1>
        <a href="{{ route('client.invoices.index') }}" class="muted" style="font-size:13px">&larr; Back to invoices</a>
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
        <div class="dl" style="margin-top:14px">
            <div><span class="k">Supplier</span><span class="v">{{ $invoice->supplier_name ?: '—' }}</span></div>
            <div><span class="k">ABN</span><span class="v">{{ $invoice->abn ?: '—' }}</span></div>
            <div><span class="k">Category</span><span class="v">{{ $invoice->category ?: '—' }}</span></div>
            <div><span class="k">Invoice date</span><span class="v">{{ $invoice->invoice_date?->format('M d, Y') ?? '—' }}</span></div>
            <div><span class="k">Due date</span><span class="v">{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</span></div>
            <div><span class="k">Invoice amount</span><span class="v">{{ $invoice->invoice_amount !== null ? $invoice->currency.' '.$invoice->invoice_amount : '—' }}</span></div>
            <div><span class="k">GST amount</span><span class="v">{{ $invoice->gst_amount !== null ? $invoice->currency.' '.$invoice->gst_amount : '—' }}</span></div>
        </div>
        @if($invoice->description)
            <div class="dl-note"><span class="k">Description</span><p class="v">{{ $invoice->description }}</p></div>
        @endif
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
        <h2 style="margin-top:20px">File details</h2>
        <div class="file-meta">
            <span>Uploaded: {{ $invoice->created_at->format('M d, Y h:i A') }}</span>
            <span>Size: {{ number_format($invoice->original_size / 1024, 1) }} KB</span>
            <span>Type: {{ strtoupper(pathinfo($invoice->original_filename, PATHINFO_EXTENSION)) }}</span>
        </div>
    </div>
</section>
@endsection
