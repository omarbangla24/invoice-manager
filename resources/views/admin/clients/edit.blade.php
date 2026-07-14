@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Edit Client</h1>
        <p class="muted">{{ $client->business_name }}</p>
    </div>
    <a class="btn secondary" href="{{ route('admin.clients.show', $client) }}">Back</a>
</div>
@if($errors->any())
    <div class="alert err">{{ $errors->first() }}</div>
@endif
<section class="card">
    <form class="form" method="post" action="{{ route('admin.clients.update', $client) }}">
        @csrf
        @method('patch')
        <div class="field"><label>Business name</label><input class="input" name="business_name" value="{{ old('business_name', $client->business_name) }}" required></div>
        <div class="field"><label>Contact name</label><input class="input" name="contact_name" value="{{ old('contact_name', $client->contact_name) }}"></div>
        <div class="field"><label>Login email</label><input class="input" name="email" type="email" value="{{ old('email', $client->user->email) }}" required></div>
        <div class="field"><label>New password</label><input class="input" name="password" type="password" placeholder="Leave blank to keep current"></div>
        <div class="field"><label>Phone</label><input class="input" name="phone" value="{{ old('phone', $client->phone) }}"></div>
        <div class="field"><label>Details</label><textarea class="textarea" name="details">{{ old('details', $client->details) }}</textarea></div>
        <button class="btn" type="submit">Save Client</button>
    </form>
</section>
@endsection
