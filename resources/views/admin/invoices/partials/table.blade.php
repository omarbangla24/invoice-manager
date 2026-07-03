<table class="table">
    <thead><tr><th>Invoice</th><th>Client</th><th>Status</th><th>Source</th><th>Date</th></tr></thead>
    <tbody>
    @forelse($invoices as $invoice)
        <tr>
            <td><a href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->title }}</a></td>
            <td>{{ $invoice->clientProfile->business_name }}</td>
            <td><span class="badge {{ $invoice->status->value }}">{{ $invoice->status->label() }}</span></td>
            <td>{{ ucfirst($invoice->source) }}</td>
            <td>{{ $invoice->created_at->format('M d, Y') }}</td>
        </tr>
    @empty
        <tr><td colspan="5" class="muted">No invoices yet.</td></tr>
    @endforelse
    </tbody>
</table>
