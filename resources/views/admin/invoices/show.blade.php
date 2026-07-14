@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1" title="{{ $invoice->original_filename }}">{{ \Illuminate\Support\Str::limit($invoice->original_filename, 48) }}</h1>
        <p class="muted">{{ $invoice->clientProfile->business_name }} · <span class="badge {{ $invoice->status->value }}">{{ $invoice->status->label() }}</span></p>
    </div>
    <div class="actions">
        <button class="btn" type="button" data-modal-open="modal-status">Update status</button>
        <button class="btn secondary" type="button" data-modal-open="modal-details">Edit details</button>
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

@if($errors->any())
    <div class="alert err">{{ $errors->first() }}</div>
@endif

<section class="grid two">
    <div class="card">
        <h2>Details</h2>
        <div class="dl">
            <div><span class="k">Supplier</span><span class="v">{{ $invoice->supplier_name ?: '—' }}</span></div>
            <div><span class="k">ABN</span><span class="v">{{ $invoice->abn ?: '—' }}</span></div>
            <div><span class="k">Category</span><span class="v">{{ $invoice->category ?: '—' }}</span></div>
            <div><span class="k">Source</span><span class="v">{{ ucfirst($invoice->source) }}</span></div>
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
        <h2>File</h2>
        <div class="file-meta">
            <span>Original size: {{ number_format($invoice->original_size / 1024, 1) }} KB</span>
            <span>Compressed size: {{ $invoice->compressed_size ? number_format($invoice->compressed_size / 1024, 1).' KB' : 'Not compressed' }}</span>
            <span>Optimization: {{ ucfirst($invoice->optimization_status) }}{{ $invoice->optimization_notes ? ' - '.$invoice->optimization_notes : '' }}</span>
            <span>Disk: {{ strtoupper($invoice->storage_disk) }}</span>
            <span>Uploaded: {{ $invoice->created_at->format('M d, Y h:i A') }}</span>
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

{{-- Status & Reply modal --}}
<div class="modal-overlay" id="modal-status" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-label="Update status">
        <div class="modal-head"><h3>Update status &amp; reply</h3><button class="modal-x" type="button" data-modal-close aria-label="Close">&times;</button></div>
        <form class="form modal-body" method="post" action="{{ route('admin.invoices.update', $invoice) }}">
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
            <div class="modal-actions">
                <button class="btn secondary" type="button" data-modal-close>Cancel</button>
                <button class="btn" type="submit">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Invoice details modal --}}
<div class="modal-overlay" id="modal-details" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-label="Edit invoice details">
        <div class="modal-head"><h3>Invoice details</h3><button class="modal-x" type="button" data-modal-close aria-label="Close">&times;</button></div>
        <form class="form modal-body" method="post" action="{{ route('admin.invoices.details.update', $invoice) }}">
            @csrf
            @method('patch')
            <div class="settings-row">
                <div class="field"><label>Supplier name</label><input class="input" name="supplier_name" value="{{ old('supplier_name', $invoice->supplier_name) }}"></div>
                <div class="field"><label>ABN</label><input class="input" name="abn" value="{{ old('abn', $invoice->abn) }}"></div>
            </div>
            <div class="field"><label>Category</label><input class="input" name="category" value="{{ old('category', $invoice->category) }}" placeholder="e.g. Utilities, Rent, Supplies"></div>
            <div class="settings-row">
                <div class="field"><label>Invoice date</label><input class="input" name="invoice_date" type="date" value="{{ old('invoice_date', $invoice->invoice_date?->format('Y-m-d')) }}"></div>
                <div class="field"><label>Due date</label><input class="input" name="due_date" type="date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}"></div>
            </div>
            <div class="settings-row">
                <div class="field"><label>Invoice amount</label><input class="input" name="invoice_amount" type="number" min="0" step="0.01" value="{{ old('invoice_amount', $invoice->invoice_amount) }}"></div>
                <div class="field"><label>GST amount</label><input class="input" name="gst_amount" type="number" min="0" step="0.01" value="{{ old('gst_amount', $invoice->gst_amount) }}"></div>
            </div>
            <div class="modal-actions">
                <button class="btn secondary" type="button" data-modal-close>Cancel</button>
                <button class="btn" type="submit">Save details</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
    <script>document.addEventListener('DOMContentLoaded',()=>{var m=document.getElementById('modal-details');if(m){m.hidden=false;document.body.style.overflow='hidden';}});</script>
@endif
@endsection
