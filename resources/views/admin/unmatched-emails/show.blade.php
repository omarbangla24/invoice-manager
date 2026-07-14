@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">{{ $email->subject ?: 'Unmatched email' }}</h1>
        <p class="muted">From {{ $email->from_email }} • To {{ $email->to_email ?: 'unknown inbox' }}</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a class="btn secondary" href="{{ route('admin.unmatched-emails.index') }}">Back to Queue</a>
        <form method="post" action="{{ route('admin.unmatched-emails.destroy', $email) }}" onsubmit="return confirm('Delete this email and all its attachments?')">
            @csrf
            @method('delete')
            <button class="btn danger" type="submit">Delete Email</button>
        </form>
    </div>
</div>
<section class="card">
    <h2>Email details</h2>
    <div class="file-meta">
        <span>Provider: {{ $email->provider ?: 'Not provided' }}</span>
        <span>Message ID: {{ $email->message_id ?: 'Not provided' }}</span>
        <span>Received: {{ $email->created_at->format('M d, Y h:i A') }}</span>
        <span>Status: {{ ucfirst($email->status) }}</span>
    </div>
</section>
<section class="card" style="margin-top:16px">
    <h2>Attachments</h2>
    <table class="table">
        <thead><tr><th>File</th><th>Size</th><th>Status</th><th>Transfer</th><th></th></tr></thead>
        <tbody>
        @forelse($email->attachments as $attachment)
            <tr>
                <td>
                    <a href="{{ route('admin.unmatched-attachments.download', $attachment) }}">{{ $attachment->original_filename }}</a>
                    <div class="muted">{{ $attachment->stored_path }}</div>
                </td>
                <td>{{ number_format($attachment->size / 1024, 1) }} KB</td>
                <td>{{ ucfirst($attachment->status) }}</td>
                <td>
                    @if($attachment->status === 'unmatched')
                        <form class="form" method="post" action="{{ route('admin.unmatched-attachments.transfer', $attachment) }}">
                            @csrf
                            @method('patch')
                            <div class="field">
                                <select class="select" name="client_profile_id" required>
                                    <option value="">Select client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->business_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field"><textarea class="textarea" name="description" placeholder="Optional transfer note"></textarea></div>
                            <button class="btn" type="submit">Transfer to Client</button>
                        </form>
                    @else
                        <span class="muted">Transferred</span>
                    @endif
                </td>
                <td>
                    @if($attachment->status === 'unmatched')
                        <form method="post" action="{{ route('admin.unmatched-attachments.destroy', $attachment) }}" onsubmit="return confirm('Delete this attachment?')">
                            @csrf
                            @method('delete')
                            <button class="btn danger" type="submit" style="padding:4px 10px;font-size:13px">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">No attachments found.</td></tr>
        @endforelse
        </tbody>
    </table>
</section>
@endsection
