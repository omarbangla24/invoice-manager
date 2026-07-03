<table class="table">
    <thead><tr><th>Invoice</th><th>Status</th><th>Amount</th><th>Uploaded</th></tr></thead>
    <tbody>
    @forelse($invoices as $invoice)
        <tr>
            <td><a href="{{ route('client.invoices.show', $invoice) }}">{{ $invoice->title }}</a></td>
            <td><span class="badge {{ $invoice->status->value }}">{{ $invoice->status->label() }}</span></td>
            <td>{{ $invoice->amount ? $invoice->currency.' '.$invoice->amount : 'Not provided' }}</td>
            <td>{{ $invoice->created_at->format('M d, Y') }}</td>
        </tr>
    @empty
        <tr><td colspan="4" class="muted">No invoices uploaded yet.</td></tr>
    @endforelse
    </tbody>
</table>
