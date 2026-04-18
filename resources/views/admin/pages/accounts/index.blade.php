@extends('admin.layouts.app')

@section('content')

{{-- Success Alert --}}
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false
    });
</script>
@endif

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Expense/Income Management</h4>
        <a href="javascript:void(0)" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">Add Accounts Items</a>
        @include('admin.pages.accounts.modal.create')
    </div>

    {{-- FILTER --}}
    <div class="card-body">

        <form method="GET" class="row g-2 mb-3">
            <div class="row mb-3">

                {{-- TOTAL INCOME --}}
                <div class="col-md-3">
                    <div class="card text-white bg-success shadow-sm">
                        <div class="card-body">
                            <h6>Total Income</h6>
                            <h4>{{ number_format($totalIncome, 2) }}</h4>
                        </div>
                    </div>
                </div>

                {{-- TOTAL INVOICE --}}
                <div class="col-md-3">
                    <div class="card text-white bg-primary shadow-sm">
                        <div class="card-body">
                            <h6>Shares Income</h6>
                            <h4>{{ number_format($totalInvoice, 2) }}</h4>
                        </div>
                    </div>
                </div>

                {{-- TOTAL EXPENSE --}}
                <div class="col-md-3">
                    <div class="card text-white bg-danger shadow-sm">
                        <div class="card-body">
                            <h6>Total Expense</h6>
                            <h4>{{ number_format($totalExpense, 2) }}</h4>
                        </div>
                    </div>
                </div>


                {{-- ADDITIONAL INCOME --}}
                <div class="col-md-3">
                    <div class="card text-white bg-dark shadow-sm">
                        <div class="card-body">
                            <h6>Net Profit</h6>
                            <h4>{{ number_format($netProfit, 2) }}</h4>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Date Range --}}
            <div class="col-md-3">
                <input type="text" name="date_range" class="form-control date-range" placeholder="Select Date Range" value="{{ request('date_range') }}">
            </div>

            {{-- Type + Category --}}
            <div class="col-md-2">
                <select name="filter" class="form-control">

                    <option value="">All</option>

                    {{-- TYPE --}}
                    <option value="income" {{ request('filter') == 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ request('filter') == 'expense' ? 'selected' : '' }}>Expense</option>

                    {{-- STATIC --}}
                    <option value="cat_Invoice Payment" {{ request('filter') == 'cat_Invoice Payment' ? 'selected' : '' }}>Invoice Payment</option>

                    {{-- Dynamic CATEGORIES --}}
                    @foreach($categories ?? [] as $cat)
                        <option value="cat_{{ $cat->name }}"
                            {{ request('filter') == 'cat_'.$cat->name ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach

                    <option value="cat_Withdraw Charge" {{ request('filter') == 'cat_Withdraw Charge' ? 'selected' : '' }}>Withdraw Charge</option>
                    <option value="cat_Withdraw" {{ request('filter') == 'cat_Withdraw' ? 'selected' : '' }}>Withdraw</option>
                </select>
            </div>

            {{-- Search --}}
            <div class="col-md-3">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
            </div>

            {{-- Buttons --}}
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-primary "><i class="fas fa-filter"></i></button>
                <a href="{{ route('admin.accounts.index') }}" class="btn btn-secondary"><i class="fas fa-undo"></i></a>
            </div>
            <div class="col-md-2">
                 <div class="dropdown">
                    <button class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-file-export"></i> Export
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.accounts.export', ['type'=>'pdf'] + request()->query()) }}">
                                <i class="fas fa-file-pdf text-danger"></i> PDF
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.accounts.export', ['type'=>'excel'] + request()->query()) }}">
                                <i class="fas fa-file-excel text-success"></i> Excel
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.accounts.export', ['type'=>'csv'] + request()->query()) }}">
                                <i class="fas fa-file-alt text-primary"></i> CSV
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

        </form>

        {{-- TABLE --}}
        <div class="table-responsive">

            <table class="table table-striped table-hover">

                <thead class="thead-dark">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Note</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($accountsData as $account)

                        <tr>

                            <td>{{ $account['date'] }}</td>

                            <td>
                                <span class="badge bg-{{ $account['type'] == 'income' ? 'success' : 'danger' }}">
                                    {{ ucfirst($account['type']) }}
                                </span>
                            </td>

                            <td>{{ $account['category'] }}</td>

                            <td>
                                <strong>{{ number_format($account['amount'], 2) }}</strong>
                            </td>

                            <td>{{ $account['note'] ?? '-' }}</td>

                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- ONLY MANUAL DATA --}}
                                    @if(isset($account['id']) && $account['is_manual'] ?? false)

                                        <div class="d-flex gap-1">

                                            {{-- <a href="{{ route('admin.accounts.edit', $account['id']) }}"
                                            class="btn btn-sm btn-info">
                                                Edit
                                            </a> --}}
                                            <button class="btn btn-sm btn-info editBtn" data-id="{{ $account['id'] }}" data-type="{{ $account['type'] }}" data-category="{{ $account['category_id'] ?? '' }}" data-date="{{ $account['date'] }}" data-amount="{{ $account['amount'] }}" data-note="{{ $account['note'] }}" data-bs-toggle="modal" data-bs-target="#editModal" >
                                                Edit
                                            </button>
                                            @include('admin.pages.accounts.modal.edit')

                                            <button class="btn btn-danger btn-sm deleteBtn"
                                                    data-url="{{ route('admin.accounts.destroy', $account['id']) }}"
                                                    data-name="{{ $account['type'] }} with amount {{ number_format($account['amount'], 2) }} on {{ $account['date'] }}">
                                                Delete
                                            </button>
                                                @include('admin.modal.confirmationmodal')
                                        </div>

                                    @elseif($account['is_invoice'] ?? false)

                                        <a href="{{ route('admin.print.invoice', $account['invoice_id']) }}"
                                        target="_blank"
                                        class="btn btn-sm btn-primary">

                                            <i class="fas fa-print"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </div>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No accounts found</td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        <div class="mt-3">
            {{ $accountsData->appends(request()->query())->links('admin.layouts.partials.__pagination') }}
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
function getMonths() {
    return window.innerWidth < 768 ? 1 : 2;
}

let fp = flatpickr(".date-range", {
    mode: "range",
    dateFormat: "Y-m-d",
    showMonths: getMonths(),
    onReady: function(selectedDates, dateStr, instance) {
        window.addEventListener("resize", function () {
            instance.set("showMonths", getMonths());
        });
    }
});
</script>

<style>
@media (max-width: 768px) {
    .flatpickr-calendar {
        width: 100% !important;
    }

    .flatpickr-rContainer {
        width: 100% !important;
    }
}
</style>
@endsection


