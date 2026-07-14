@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">Upload Invoice</h1>
        <p class="muted">Images are compressed automatically when the server supports it.</p>
    </div>
</div>
@if($errors->any())
    <div class="alert err">{{ $errors->first() }}</div>
@endif
<section class="card">
    <form class="form" method="post" action="{{ route('client.invoices.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="field"><label>File</label><input class="input" name="invoice_file" type="file" required></div>
        <div class="field"><label>Description <span class="muted">(optional)</span></label><textarea class="textarea" name="description">{{ old('description') }}</textarea></div>
        <button class="btn" type="submit">Upload</button>
    </form>
</section>
@endsection
