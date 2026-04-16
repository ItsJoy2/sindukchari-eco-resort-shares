@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">Invoices History</div>
    </div>

    <div class="card-body table-responsive">

        {{-- FILTER --}}
        <form action="{{ route('admin.invoices.index') }}" method="GET"
              class="mb-3 d-flex align-items-center gap-2 flex-wrap">

            <input type="text" name="search" class="form-control w-auto" placeholder="Search email or invoice no" value="{{ request('search') }}">

            <select name="status" class="form-select w-auto">
                <option value="">All Status</option>
                <option value="paid" {{ request('status')=='paid'?'selected':'' }}>Paid</option>
                <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
            </select>

            <button type="submit" class="btn btn-primary">Search</button>

            @if(request()->has('email') || request()->has('status'))
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </form>

        {{-- TABLE --}}
        <table class="table table-striped table-hover mt-4">

            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Invoice No</th>
                    <th>User</th>
                    <th>Share Name</th>
                    <th>Amount</th>
                    <th>Discount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($invoices as $index => $invoice)

                    <tr>
                        <td>{{ $invoices->firstItem() + $index }}</td>

                        <td>{{ $invoice->invoice_no }}</td>

                        <td>
                            {{ $invoice->user->name ?? 'N/A' }}<br>
                            <small class="text-muted">{{ $invoice->user->email ?? '' }}</small>
                        </td>

                        <td>
                            {{ $invoice->investor->package->share_name ?? 'N/A' }}
                        </td>

                        <td>৳{{ number_format($invoice->amount,2) }}</td>

                        <td>৳{{ number_format($invoice->discount_amount,2) }}</td>

                        <td>
                            <span class="badge
                                {{ $invoice->status=='paid' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>

                        <td>
                            {{ $invoice->created_at->format('Y-m-d') }}
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="8" class="text-center">No invoices found.</td>
                    </tr>
                @endforelse

            </tbody>

        </table>

        {{-- PAGINATION --}}
        <div class="d-flex justify-content-center">
            {{ $invoices->appends(request()->query())->links('admin.layouts.partials.__pagination') }}
        </div>

    </div>
</div>
@endsection
