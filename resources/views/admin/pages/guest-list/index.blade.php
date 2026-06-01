@extends('admin.layouts.app')

@section('content')

<div class="card">

<div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="card-title mb-0">Guest List</h4>

    <button type="button"
            class="btn btn-success"
            data-bs-toggle="modal"
            data-bs-target="#createGuestModal">
        <i class="fas fa-plus"></i> Add Guest
    </button>
</div>

<div class="card-body">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

        <!-- LEFT SIDE: Search Form -->
        <form method="GET" class="row g-2 flex-grow-1">

            <div class="col-md-3">
                <input type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by name"
                    value="{{ request('search') }}">
            </div>

            <div class="col-md-3">
                <select name="status" class="form-control">
                    <option value="">All Status</option>

                    <option value="Interested" {{ request('status') == 'Interested' ? 'selected' : '' }}>
                        Interested
                    </option>

                    <option value="Highly Motivated" {{ request('status') == 'Highly Motivated' ? 'selected' : '' }}>
                        Highly Motivated
                    </option>

                    <option value="Not Interested" {{ request('status') == 'Not Interested' ? 'selected' : '' }}>
                        Not Interested
                    </option>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">

                <!-- Search Button -->
                <button class="btn btn-primary w-10">
                    <i class="fas fa-search"></i>
                </button>

                <!-- Reset Button -->
                <a href="{{ route('admin.guest-list.index') }}"
                class="btn btn-secondary w-10">
                    <i class="fas fa-undo"></i>
                </a>

            </div>

        </form>

        <!-- RIGHT SIDE: Export Dropdown -->
        <div class="dropdown ms-auto">

            <button class="btn btn-dark dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown">

                <i class="fas fa-download"></i> Export
            </button>

            <ul class="dropdown-menu dropdown-menu-end">

                <li>
                    <a class="dropdown-item"
                    href="{{ route('admin.guest-list.export', 'pdf') }}">
                        <i class="fas fa-file-pdf text-danger"></i> PDF
                    </a>
                </li>

                <li>
                    <a class="dropdown-item"
                    href="{{ route('admin.guest-list.export', 'csv') }}">
                        <i class="fa-solid fa-file-csv text-success"></i> CSV
                    </a>
                </li>

                <li>
                    <a class="dropdown-item"
                    href="{{ route('admin.guest-list.export', 'excel') }}">
                        <i class="fas fa-file-excel text-primary"></i> Excel
                    </a>
                </li>

            </ul>

        </div>

    </div>

    <div class="table-responsive">

        <table class="table table-striped table-hover mt-3">

            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Address</th>
                    <th>Profession</th>
                    <th>Status</th>
                    <th>Reference</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

            @forelse($guestLists as $guest)

                <tr>

                    <td>
                        {{ $loop->iteration + $guestLists->firstItem() - 1 }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($guest->date)->format('d M Y') }}
                    </td>

                    <td>
                        {{ $guest->name }}
                    </td>

                    <td>
                        {{ $guest->mobile }}
                    </td>

                    <td>
                        {{ $guest->address }}
                    </td>

                    <td>
                        {{ $guest->profession }}
                    </td>

                    <td>

                        @if($guest->status == 'Interested')
                            <span class="badge bg-info">
                                Interested
                            </span>

                        @elseif($guest->status == 'Highly Motivated')
                            <span class="badge bg-success">
                                Highly Motivated
                            </span>

                        @else
                            <span class="badge bg-danger">
                                Not Interested
                            </span>
                        @endif

                    </td>

                    <td>
                        {{ $guest->reference }}
                    </td>

                    <td>
                        <button
                            type="button"
                            class="btn btn-sm btn-primary viewGuestBtn"

                            data-date="{{ \Carbon\Carbon::parse($guest->date)->format('d M Y') }}"
                            data-name="{{ $guest->name }}"
                            data-mobile="{{ $guest->mobile }}"
                            data-address="{{ $guest->address }}"
                            data-profession="{{ $guest->profession }}"
                            data-status="{{ $guest->status }}"
                            data-reference="{{ $guest->reference }}"
                            data-note="{{ $guest->note }}"

                            data-bs-toggle="modal"
                            data-bs-target="#viewGuestModal">

                            <i class="fas fa-eye"></i>
                        </button>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="9" class="text-center">
                        No guest found
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-3">
        {{ $guestLists->withQueryString()->links('admin.layouts.partials.__pagination') }}
    </div>

</div>
```

</div>

@include('admin.pages.guest-list.__createGuestModel')
@include('admin.pages.guest-list.__showDetailsModel')

@if(session('success'))

<script>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '{{ session('success') }}',
    timer: 2500,
    showConfirmButton: false
});
</script>

@endif

@if(session('error'))

<script>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '{{ session('error') }}',
    timer: 2500,
    showConfirmButton: false
});
</script>

@endif

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.viewGuestBtn').forEach(button => {

        button.addEventListener('click', function () {

            document.getElementById('guest_date').innerText =
                this.dataset.date || 'N/A';

            document.getElementById('guest_name').innerText =
                this.dataset.name || 'N/A';

            document.getElementById('guest_mobile').innerText =
                this.dataset.mobile || 'N/A';

            document.getElementById('guest_address').innerText =
                this.dataset.address || 'N/A';

            document.getElementById('guest_profession').innerText =
                this.dataset.profession || 'N/A';

            document.getElementById('guest_reference').innerText =
                this.dataset.reference || 'N/A';

            document.getElementById('guest_note').innerText =
                this.dataset.note || 'No note available.';

            let status = this.dataset.status;
            let badge = '';

            if(status === 'Interested'){
                badge = '<span class="badge bg-info">Interested</span>';
            }
            else if(status === 'Highly Motivated'){
                badge = '<span class="badge bg-success">Highly Motivated</span>';
            }
            else{
                badge = '<span class="badge bg-danger">Not Interested</span>';
            }

            document.getElementById('guest_status').innerHTML = badge;
        });

    });

});
</script>

@endsection
