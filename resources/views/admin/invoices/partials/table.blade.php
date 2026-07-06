<table class="table">
    <thead><tr><th>Invoice</th><th>Client</th><th>Status</th><th>Source</th><th>Date</th><th></th></tr></thead>
    <tbody>
    @forelse($invoices as $invoice)
        <tr>
            <td><a href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->title }}</a></td>
            <td>{{ $invoice->clientProfile->business_name }}</td>
            <td><span class="badge {{ $invoice->status->value }}">{{ $invoice->status->label() }}</span></td>
            <td>{{ ucfirst($invoice->source) }}</td>
            <td>{{ $invoice->created_at->format('M d, Y') }}</td>
            <td>
                <form method="post" action="{{ route('admin.invoices.destroy', $invoice) }}" onsubmit="return confirm('Delete this invoice?')">
                    @csrf
                    @method('delete')
                    <button class="btn danger" type="submit" style="padding:4px 10px;font-size:13px">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="muted">No invoices yet.</td></tr>
    @endforelse
    </tbody>
</table>
