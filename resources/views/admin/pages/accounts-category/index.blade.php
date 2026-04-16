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
            <a href="{{ route('admin.accounts-category.create') }}" class="btn btn-success btn-sm">
                + Add Category
            </a>
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
                                <a href="{{ route('admin.accounts-category.edit', $category->id) }}"
                                   class="btn btn-sm btn-info">
                                   Edit
                                </a>

                                <form action="{{ route('admin.accounts-category.destroy', $category->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>
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
