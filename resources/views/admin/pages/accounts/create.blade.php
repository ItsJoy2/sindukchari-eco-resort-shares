@extends('admin.layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Create Account</h4>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('admin.accounts.store') }}">
            @csrf

            {{-- Title --}}
            <div class="form-group mb-2">
                <label>Title</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>

                @error('title')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Type --}}
            <div class="form-group mb-2">
                <label>Type</label>
                <select name="type" class="form-control @error('type') is-invalid @enderror" required>

                    <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>
                        Income
                    </option>

                    <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>
                        Expense
                    </option>
                </select>

                @error('type')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Category --}}
            <div class="form-group mb-2">
                <label>Category</label>
                <select name="category_id"
                        class="form-control @error('category_id') is-invalid @enderror"
                        required>

                    <option value="" disabled selected>Select Category</option>

                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach

                </select>

                @error('category_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Date --}}
            <div class="form-group mb-2">
                <label>Date</label>
                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" required>

                @error('date')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Amount --}}
            <div class="form-group mb-2">
                <label>Amount</label>
                <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>

                @error('amount')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Note --}}
            <div class="form-group mb-3">
                <label>Note</label>
                <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="3">{{ old('note') }}</textarea>

                @error('note')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                Create
            </button>

        </form>

    </div>
</div>

@endsection
