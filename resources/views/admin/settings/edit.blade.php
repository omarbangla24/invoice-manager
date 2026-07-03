@extends('layouts.app')

@section('content')
<section class="settings-hero">
    <div>
        <h1 class="h1">Settings</h1>
        <p class="muted">Admin account, outgoing mail, and Cloudflare inbound invoice routing.</p>
    </div>
    <span class="settings-pill">Webhook: {{ url('/inbound/email') }}</span>
</section>
@if($errors->any())
    <div class="alert err">{{ $errors->first() }}</div>
@endif
<nav class="settings-tabs" aria-label="Settings sections">
    <a class="settings-tab {{ $tab === 'account' ? 'active' : '' }}" href="{{ route('admin.settings.edit', ['tab' => 'account']) }}">
        <strong>Admin Account</strong>
        <span>Login email and password</span>
    </a>
    <a class="settings-tab {{ $tab === 'email' ? 'active' : '' }}" href="{{ route('admin.settings.edit', ['tab' => 'email']) }}">
        <strong>Email Setup</strong>
        <span>SMTP and Cloudflare inbound</span>
    </a>
    <a class="settings-tab {{ $tab === 'storage' ? 'active' : '' }}" href="{{ route('admin.settings.edit', ['tab' => 'storage']) }}">
        <strong>Storage</strong>
        <span>Local, S3, or Cloudflare R2</span>
    </a>
</nav>
<section>
    @if($tab === 'account')
    <article class="card settings-card">
        <div class="settings-card-head">
            <span class="section-kicker">Security</span>
            <h2>Admin Account</h2>
            <p class="muted">Change login email or password with current password check.</p>
        </div>
        <div class="settings-body">
            <form class="form" method="post" action="{{ route('admin.settings.profile.update') }}">
                @csrf
                @method('patch')
                <div class="field">
                    <label>Name</label>
                    <input class="input" name="name" value="{{ old('name', $admin->name) }}" required>
                </div>
                <div class="field">
                    <label>Email</label>
                    <input class="input" name="email" type="email" value="{{ old('email', $admin->email) }}" required>
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
                    <button class="btn" type="submit">Save Account</button>
                </div>
            </form>
        </div>
    </article>
    @elseif($tab === 'email')
    <article class="card settings-card">
        <div class="settings-card-head">
            <span class="section-kicker">Mail</span>
            <h2>Email Setup</h2>
            <p class="muted">SMTP sends notifications. Cloudflare Worker receives invoices.</p>
        </div>
        <div class="settings-body">
            <form class="form" method="post" action="{{ route('admin.settings.email.update') }}">
                @csrf
                @method('patch')

                <div class="settings-section">
                    <div class="section-title">
                        <h3>Outgoing SMTP</h3>
                        <span class="muted">Send email</span>
                    </div>
                    <div class="settings-row">
                        <div class="field">
                            <label>Mailer</label>
                            <select class="select" name="mail_mailer">
                                @foreach(['smtp', 'log', 'ses', 'postmark', 'resend'] as $mailer)
                                    <option value="{{ $mailer }}" @selected(old('mail_mailer', $settings['mail_mailer']) === $mailer)>{{ strtoupper($mailer) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Port</label>
                            <input class="input" name="mail_port" type="number" min="1" max="65535" value="{{ old('mail_port', $settings['mail_port']) }}">
                        </div>
                    </div>
                    <div class="field"><label>Host</label><input class="input" name="mail_host" value="{{ old('mail_host', $settings['mail_host']) }}" placeholder="smtp.example.com"></div>
                    <div class="settings-row">
                        <div class="field"><label>Username</label><input class="input" name="mail_username" value="{{ old('mail_username', $settings['mail_username']) }}"></div>
                        <div class="field"><label>Password</label><input class="input" name="mail_password" type="password" placeholder="Leave blank to keep current"></div>
                    </div>
                    <div class="settings-row">
                        <div class="field"><label>From email</label><input class="input" name="mail_from_address" type="email" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" required></div>
                        <div class="field"><label>From name</label><input class="input" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}" required></div>
                    </div>
                </div>

                <div class="settings-section">
                    <div class="section-title">
                        <h3>Inbound Invoice Email</h3>
                        <span class="muted">Receive files</span>
                    </div>
                    <div class="settings-row">
                        <div class="field"><label>Receiving email</label><input class="input" name="inbound_email_address" type="email" value="{{ old('inbound_email_address', $settings['inbound_email_address']) }}" required></div>
                        <div class="field"><label>Provider</label><input class="input" name="inbound_provider" value="{{ old('inbound_provider', $settings['inbound_provider']) }}" placeholder="Cloudflare Email Routing" required></div>
                    </div>
                    <div class="field"><label>Webhook token</label><input class="input" name="inbound_email_token" value="{{ old('inbound_email_token', $settings['inbound_email_token']) }}" required minlength="12"></div>
                    <div class="callout">
                        <strong>Cloudflare Worker target</strong>
                        <span class="code-line">{{ url('/inbound/email') }}</span>
                        <span>Header: <strong>Authorization: Bearer webhook-token</strong></span>
                    </div>
                </div>

                <div class="settings-section">
                    <div class="section-title">
                        <h3>Free Cloudflare Checklist</h3>
                        <span class="muted">DNS required</span>
                    </div>
                    <ul class="mini-steps">
                        <li><span class="step-dot">1</span><span>Cloudflare DNS active for domain.</span></li>
                        <li><span class="step-dot">2</span><span>Email Routing onboarded for domain.</span></li>
                        <li><span class="step-dot">3</span><span>Routing rule: <strong>invoices</strong> -> Worker.</span></li>
                        <li><span class="step-dot">4</span><span>Worker secret equals token above.</span></li>
                    </ul>
                </div>

                <div class="form-actions">
                    <button class="btn" type="submit">Save Email Setup</button>
                </div>
            </form>
        </div>
    </article>
    @else
    <article class="card settings-card">
        <div class="settings-card-head">
            <span class="section-kicker">Storage</span>
            <h2>Cloud Storage</h2>
            <p class="muted">Choose local disk or S3-compatible storage such as Cloudflare R2.</p>
        </div>
        <div class="settings-body">
            <form class="form" method="post" action="{{ route('admin.settings.storage.update') }}">
                @csrf
                @method('patch')
                <div class="settings-section">
                    <div class="section-title">
                        <h3>Active Disk</h3>
                        <span class="muted">New uploads only</span>
                    </div>
                    <div class="field">
                        <label>Storage disk</label>
                        <select class="select" name="storage_disk">
                            <option value="local" @selected(old('storage_disk', $settings['storage_disk']) === 'local')>LOCAL</option>
                            <option value="s3" @selected(old('storage_disk', $settings['storage_disk']) === 's3')>S3 / Cloudflare R2</option>
                        </select>
                    </div>
                </div>
                <div class="settings-section">
                    <div class="section-title">
                        <h3>S3 / R2 Credentials</h3>
                        <span class="muted">Encrypted in DB</span>
                    </div>
                    <div class="settings-row">
                        <div class="field"><label>Access key</label><input class="input" name="s3_key" value="{{ old('s3_key', $settings['s3_key']) }}"></div>
                        <div class="field"><label>Secret key</label><input class="input" name="s3_secret" type="password" placeholder="Leave blank to keep current"></div>
                    </div>
                    <div class="settings-row">
                        <div class="field"><label>Region</label><input class="input" name="s3_region" value="{{ old('s3_region', $settings['s3_region']) }}" placeholder="auto or us-east-1"></div>
                        <div class="field"><label>Bucket</label><input class="input" name="s3_bucket" value="{{ old('s3_bucket', $settings['s3_bucket']) }}"></div>
                    </div>
                    <div class="field"><label>Endpoint</label><input class="input" name="s3_endpoint" value="{{ old('s3_endpoint', $settings['s3_endpoint']) }}" placeholder="https://ACCOUNT_ID.r2.cloudflarestorage.com"></div>
                    <div class="field"><label>Public URL</label><input class="input" name="s3_url" value="{{ old('s3_url', $settings['s3_url']) }}"></div>
                    <label><input type="checkbox" name="s3_path_style" value="1" @checked(old('s3_path_style', $settings['s3_path_style']) === 'true')> Use path-style endpoint</label>
                </div>
                <div class="callout">
                    <strong>Cloudflare R2 note</strong>
                    <span>Use S3-compatible API endpoint. Existing local files stay local; new uploads use selected disk.</span>
                </div>
                <div class="form-actions">
                    <button class="btn" type="submit">Save Storage</button>
                </div>
            </form>
        </div>
    </article>
    @endif
</section>
@endsection
