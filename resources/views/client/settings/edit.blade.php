@extends('layouts.app')

@section('content')
<section class="settings-hero">
    <div>
        <h1 class="h1">Portal Settings</h1>
        <p class="muted">{{ $client->business_name }} account login and password.</p>
    </div>
    <span class="settings-pill">{{ $user->email }}</span>
</section>
@if($errors->any())
    <div class="alert err">{{ $errors->first() }}</div>
@endif
<section class="settings-grid">
    <article class="card settings-card">
        <div class="settings-card-head">
            <span class="section-kicker">Account</span>
            <h2>Login Details</h2>
            <p class="muted">Update portal name, email, and password.</p>
        </div>
        <div class="settings-body">
            <form class="form" method="post" action="{{ route('client.settings.update') }}">
                @csrf
                @method('patch')
                <div class="field">
                    <label>Name</label>
                    <input class="input" name="name" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="field">
                    <label>Email</label>
                    <input class="input" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="settings-section">
                    <div class="section-title">
                        <h3>Password</h3>
                        <span class="muted">Optional</span>
                    </div>
                    <div class="field">
                        <label>New password</label>
                        <input class="input" name="password" type="password" autocomplete="new-password">
                    </div>
                    <div class="field">
                        <label>Confirm new password</label>
                        <input class="input" name="password_confirmation" type="password" autocomplete="new-password">
                    </div>
                </div>
                <div class="field">
                    <label>Current password</label>
                    <input class="input" name="current_password" type="password" required autocomplete="current-password">
                </div>
                <div class="form-actions">
                    <button class="btn" type="submit">Save Portal Account</button>
                </div>
            </form>
        </div>
    </article>
    <article class="card settings-card">
        <div class="settings-card-head">
            <span class="section-kicker">Business</span>
            <h2>Client Profile</h2>
            <p class="muted">Accountant controls business profile fields.</p>
        </div>
        <div class="settings-body">
            <div class="settings-section">
                <div class="file-meta">
                    <span>Business: <strong>{{ $client->business_name }}</strong></span>
                    <span>Contact: {{ $client->contact_name ?: 'Not set' }}</span>
                    <span>Folder: {{ $client->storage_folder }}</span>
                </div>
            </div>
            <div class="callout" style="margin-top:14px">
                <strong>Email invoices</strong>
                <span>Send receipts from this login email.</span>
            </div>
        </div>
    </article>
</section>
@endsection
