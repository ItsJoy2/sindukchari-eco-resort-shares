@extends('admin.layouts.app')

@section('content')

    {{-- Success Alert --}}
    @if(session('success'))
        <script>
            Swal.fire({ icon: 'success', title: 'Success!', text: '{{ session('success') }}', timer: 3000,showConfirmButton: false
            });
        </script>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Accounts Category</h4>
            <a href="javascript:void(0)" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                + Add Category
            </a>
            @include('admin.pages.accounts-category.modal.create')
        </div>

        <div class="card-body table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($categories as $index => $category)
                        <tr>
                            <td>{{ $index + 1 }}</td>

                            {{-- Name --}}
                            <td>{{ $category->name }}</td>

                            {{-- Status --}}
                            <td>
                                <span class="badge {{ $category->status ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $category->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="d-flex gap-1">
                                <button class="btn btn-sm btn-info editCategoryBtn" data-id="{{ $category->id }}" data-name="{{ $category->name }}" data-status="{{ $category->status }}" data-bs-toggle="modal" data-bs-target="#editCategoryModal" >
                                    Edit
                                </button>
                                @include('admin.pages.accounts-category.modal.edit')
                                
                                <button class="btn btn-danger btn-sm deleteBtn"
                                        data-url="{{ route('admin.accounts-category.destroy', $category->id) }}"
                                        data-name="{{ $category->name }}">
                                    Delete
                                </button>
                                    @include('admin.modal.confirmationmodal')
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No Accounts Categories found.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
            <div class="mt-3">
                {{ $categories->appends(request()->query())->links('admin.layouts.partials.__pagination') }}
            </div>
        </div>
    </div>

@endsection
