@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Job Request #{{ $jobRequest->id }}</h1>
        <p class="muted">{{ $jobRequest->clientProfile->business_name }} &middot; <span class="badge {{ $jobRequest->status->value }}">{{ $jobRequest->status->label() }}</span></p>
    </div>
    <div class="actions">
        <button class="btn" type="button" data-modal-open="modal-status">Update Status</button>
    </div>
</div>

@if($errors->any())
    <div class="alert err">{{ $errors->first() }}</div>
@endif

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
            <div><span class="k">Client</span><span class="v">{{ $jobRequest->clientProfile->business_name }}</span></div>
            <div><span class="k">Status</span><span class="v"><span class="badge {{ $jobRequest->status->value }}">{{ $jobRequest->status->label() }}</span></span></div>
            <div><span class="k">Submitted</span><span class="v">{{ $jobRequest->created_at->format('M d, Y h:i A') }}</span></div>
        </div>
        @if($jobRequest->remarks)
            <div class="dl-note"><span class="k">Remarks</span><p class="v">{{ $jobRequest->remarks }}</p></div>
        @endif
        @if($jobRequest->attachment_path)
            <div class="dl-note">
                <span class="k">Attachment</span>
                <p class="v"><a class="btn secondary" href="{{ route('admin.job-requests.attachment', $jobRequest) }}" style="padding:6px 12px;font-size:13px">{{ $jobRequest->attachment_filename }}</a></p>
            </div>
        @endif
    </div>
</section>

<div class="modal-overlay" id="modal-status" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-label="Update status">
        <div class="modal-head"><h3>Update status</h3><button class="modal-x" type="button" data-modal-close aria-label="Close">&times;</button></div>
        <form class="form modal-body" method="post" action="{{ route('admin.job-requests.update-status', $jobRequest) }}">
            @csrf
            @method('patch')
            <div class="field">
                <label>Status</label>
                <select class="select" name="status">
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected($jobRequest->status === $status)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-actions">
                <button class="btn secondary" type="button" data-modal-close>Cancel</button>
                <button class="btn" type="submit">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
