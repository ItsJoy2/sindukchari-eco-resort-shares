@extends('admin.layouts.app')

@section('content')
<div class="p-5">
    <h4>Create New Pair</h4>
    <form method="POST" action="{{ route('admin.accounts-category.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.pages.accounts-category.form')
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
