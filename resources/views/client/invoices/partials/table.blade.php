<div class="table-scroll">
<table class="table">
    <thead><tr><th>#</th><th>Invoice</th><th>Supplier</th><th>ABN</th><th>Category</th><th>Invoice date</th><th>Due date</th><th>Amount</th><th>GST</th><th>Status</th><th>Source</th><th>Uploaded</th></tr></thead>
    <tbody>
    @forelse($invoices as $invoice)
        <tr>
            <td class="cell-sl">{{ method_exists($invoices, 'firstItem') ? $invoices->firstItem() + $loop->index : $loop->iteration }}</td>
            <td><a class="cell-file" title="{{ $invoice->original_filename }}" href="{{ route('client.invoices.show', $invoice) }}">{{ \Illuminate\Support\Str::limit($invoice->original_filename, 32) }}</a></td>
            <td>{{ $invoice->supplier_name ?: '—' }}</td>
            <td>{{ $invoice->abn ?: '—' }}</td>
            <td>{{ $invoice->category ?: '—' }}</td>
            <td>{{ $invoice->invoice_date?->format('M d, Y') ?? '—' }}</td>
            <td>{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</td>
            <td>{{ $invoice->invoice_amount !== null ? $invoice->currency.' '.$invoice->invoice_amount : '—' }}</td>
            <td>{{ $invoice->gst_amount !== null ? $invoice->currency.' '.$invoice->gst_amount : '—' }}</td>
            <td><span class="badge {{ $invoice->status->value }}">{{ $invoice->status->label() }}</span></td>
            <td>{{ ucfirst($invoice->source) }}</td>
            <td>{{ $invoice->created_at->format('M d, Y') }}</td>
        </tr>
    @empty
        <tr><td colspan="12" class="muted">No invoices uploaded yet.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
