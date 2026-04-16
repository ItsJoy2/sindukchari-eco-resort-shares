@extends('admin.layouts.app')

@section('content')
<div class="p-5">
    <h4>Edit Pair</h4>
    <form method="POST" action="{{ route('admin.accounts-category.update', $category->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.pages.accounts-category.form', ['category' => $category])
        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
@endsection
