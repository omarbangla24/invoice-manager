@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Add Client</h1>
        <p class="muted">Create a client profile and portal login.</p>
    </div>
</div>
@if($errors->any())
    <div class="alert err">{{ $errors->first() }}</div>
@endif
<section class="card">
    <form class="form" method="post" action="{{ route('admin.clients.store') }}">
        @csrf
        <div class="field"><label>Business name</label><input class="input" name="business_name" value="{{ old('business_name') }}" required></div>
        <div class="field"><label>Contact name</label><input class="input" name="contact_name" value="{{ old('contact_name') }}"></div>
        <div class="field"><label>Login email</label><input class="input" name="email" type="email" value="{{ old('email') }}" required></div>
        <div class="field"><label>Temporary password</label><input class="input" name="password" type="password" required minlength="8"></div>
        <div class="field"><label>Phone</label><input class="input" name="phone" value="{{ old('phone') }}"></div>
        <div class="field"><label>Tax identifier</label><input class="input" name="tax_identifier" value="{{ old('tax_identifier') }}"></div>
        <div class="field"><label>Domain webmail address</label><input class="input" name="webmail_address" type="email" value="{{ old('webmail_address') }}" placeholder="client@yourdomain.com"></div>
        <div class="field"><label>Details</label><textarea class="textarea" name="details">{{ old('details') }}</textarea></div>
        <button class="btn" type="submit">Create Client</button>
    </form>
</section>
@endsection
