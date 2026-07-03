@extends('layouts.app')

@section('content')
<main class="login">
    <section class="card">
        <h1 class="h1">Sign in</h1>
        <p class="muted">Access the invoice collection dashboard.</p>
        @if($errors->any())
            <div class="alert err">{{ $errors->first() }}</div>
        @endif
        <form class="form" method="post" action="{{ route('login.store') }}">
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input class="input" id="password" name="password" type="password" required>
            </div>
            <label><input type="checkbox" name="remember" value="1"> Remember me</label>
            <button class="btn" type="submit">Login</button>
        </form>
    </section>
</main>
@endsection
