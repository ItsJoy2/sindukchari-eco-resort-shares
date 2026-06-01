@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">All Users</h4>

        <button type="button"
                class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#createUserModal">
            <i class="fas fa-plus"></i> Create User
        </button>
    </div>

    <div class="card-body">

        {{-- Filter + Search --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 mb-3">

            {{-- Filter --}}
            <div class="col-md-4">
                <select name="filter" class="form-control">
                    <option value="">-- Filter Users --</option>
                    <option value="active" {{ request('filter') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('filter') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="blocked" {{ request('filter') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                    <option value="unblocked" {{ request('filter') === 'unblocked' ? 'selected' : '' }}>Unblocked</option>
                </select>
            </div>

            {{-- Search --}}
            <div class="col-md-5">
                <input type="text" name="search" placeholder="Search by email"
                       value="{{ request('search') }}" class="form-control">
            </div>

            {{-- Buttons --}}
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary w-50">Apply</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary w-50">Reset</a>
            </div>
        </form>


        {{-- Users Table --}}
        <div class="table-responsive">
            <table class="table table-striped table-hover mt-3">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        {{-- <th>Registered</th> --}}
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Funding Wallet</th>
                        <th>Bonus Wallet</th>
                        {{-- <th>Refer Code</th> --}}
                        <th>Referred By</th>
                        {{-- <th>Email Verified</th> --}}
                        {{-- <th>Active</th> --}}
                        <th>Blocked</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $loop->iteration + $users->firstItem() - 1 }}</td>

                        {{-- <td>{{ $user->created_at->format('d-m-Y') }}</td> --}}

                        <td>{{ $user->name }}</td>

                        <td>{{ $user->email }}</td>

                        <td>{{ $user->mobile }}</td>

                        <td>৳{{ number_format($user->funding_wallet ?? 0, 2) }}</td>

                        <td>৳{{ number_format($user->bonus_wallet ?? 0, 2) }}</td>

                        {{-- <td>{{ $user->refer_code }}</td> --}}

                        <td>{{ $user->referredBy->name ?? 'N/A' }}</td>

                        {{-- <td>
                            <span class="badge {{ $user->email_verified_at ? 'bg-success' : 'bg-warning' }}">
                                {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                            </span>
                        </td> --}}

                        {{-- <td>
                            <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td> --}}

                        <td>
                            <span class="badge {{ $user->is_block ? 'bg-danger' : 'bg-success' }}">
                                {{ $user->is_block ? 'Blocked' : 'Unblocked' }}
                            </span>
                        </td>

                        <td>
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="13" class="text-center py-3">No users found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $users->withQueryString()->links('admin.layouts.partials.__pagination') }}
        </div>
    </div>

    {{-- SweetAlert --}}
    @if(session('success'))
    <script>
        Swal.fire({
            icon: "success",
            title: "Success!",
            text: "{{ session('success') }}",
            timer: 2500,
            showConfirmButton: false
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: "{{ session('error') }}",
            timer: 2500,
            showConfirmButton: false
        });
    </script>
    @endif
</div>




<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Create New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Name</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Mobile</label>
                            <input type="text"
                                   name="mobile"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Password</label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Sponsor ID (Refer Code)</label>
                            <input type="text"
                                   name="refer_code"
                                   class="form-control"
                                   placeholder="Optional">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Email Verification</label>
                            <select name="email_verified" class="form-control">
                                <option value="0">Not Verified</option>
                                <option value="1">Verified</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Close
                    </button>

                    <button type="submit"
                            class="btn btn-success">
                        Create User
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection
