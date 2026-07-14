@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Job Request #{{ $jobRequest->id }}</h1>
        <p class="muted"><span class="badge {{ $jobRequest->status->value }}">{{ $jobRequest->status->label() }}</span> &middot; Submitted {{ $jobRequest->created_at->format('M d, Y h:i A') }}</p>
    </div>
</div>

<section class="grid two">
    <div class="card">
        <h2>Selected Jobs</h2>
        <table class="table">
            <thead><tr><th>Job</th><th>QTY</th><th>Unit Price</th><th>Total</th></tr></thead>
            <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($jobRequest->items as $item)
                @php $lineTotal = $item->qty * $item->unit_price; $grandTotal += $lineTotal; @endphp
                <tr>
                    <td>
                        {{ $item->job->type_of_service }}
                        @if($item->job->description)
                            <br><small class="muted">{{ \Illuminate\Support\Str::limit($item->job->description, 50) }}</small>
                        @endif
                    </td>
                    <td>{{ $item->qty }}</td>
                    <td>AUD {{ number_format($item->unit_price, 2) }}</td>
                    <td>AUD {{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr><td colspan="3" style="text-align:right"><strong>Total</strong></td><td><strong>AUD {{ number_format($grandTotal, 2) }}</strong></td></tr>
            </tfoot>
        </table>
    </div>
    <div class="card">
        <h2>Details</h2>
        <div class="dl">
            <div><span class="k">Status</span><span class="v"><span class="badge {{ $jobRequest->status->value }}">{{ $jobRequest->status->label() }}</span></span></div>
            <div><span class="k">Submitted</span><span class="v">{{ $jobRequest->created_at->format('M d, Y h:i A') }}</span></div>
        </div>
        @if($jobRequest->remarks)
            <div class="dl-note"><span class="k">Remarks</span><p class="v">{{ $jobRequest->remarks }}</p></div>
        @endif
        @if($jobRequest->attachment_path)
            <div class="dl-note">
                <span class="k">Attachment</span>
                <p class="v"><a class="btn secondary" href="{{ route('client.job-requests.attachment', $jobRequest) }}" style="padding:6px 12px;font-size:13px">{{ $jobRequest->attachment_filename }}</a></p>
            </div>
        @endif
    </div>
</section>
@endsection
