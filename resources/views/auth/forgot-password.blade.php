@extends('layouts.app')

@section('content')
<main class="login">
    <section class="card">
        <h1 class="h1">Forgot Password</h1>
        <p class="muted">Password reset is handled by your administrator.</p>
        <div class="callout" style="margin-top:16px">
            <p style="margin:0">Please contact your accountant or system administrator to reset your password. They can update it from the client management panel.</p>
        </div>
        <p style="text-align:center;margin-top:20px"><a href="{{ route('login') }}" style="color:var(--primary);font-size:14px">&larr; Back to login</a></p>
    </section>
</main>
@endsection
