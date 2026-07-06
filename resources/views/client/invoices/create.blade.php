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
        <div class="field"><label>Title</label><input class="input" name="title" value="{{ old('title') }}"></div>
        <div class="field"><label>Expense date</label><input class="input" name="expense_date" type="date" value="{{ old('expense_date') }}"></div>
        <div class="field"><label>Amount</label><input class="input" name="amount" type="number" min="0" step="0.01" value="{{ old('amount') }}"></div>
        <div class="field"><label>File</label><input class="input" name="invoice_file" type="file" required></div>
        <div class="field"><label>Description</label><textarea class="textarea" name="description">{{ old('description') }}</textarea></div>
        <button class="btn" type="submit">Upload</button>
    </form>
</section>
@endsection
