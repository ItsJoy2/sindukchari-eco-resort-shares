@extends('admin.layouts.app')

@section('title', 'Bonus Settings')

@section('content')
<div class="container">
    <h2 class="mb-4">Bonus Settings</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.bonus-settings.update') }}" method="POST">
        @csrf

        <div class="row w-75">
            <!-- Left Sidebar Navigation -->
            <div class="col-md-4">
                <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist">

                    <button class="nav-link active"
                            id="v-pills-level-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#v-pills-level"
                            type="button"
                            role="tab">
                        <i class="fas fa-level-up-alt me-2"></i> Level Bonuses
                    </button>

                    <button class="nav-link"
                            id="v-pills-pool-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#v-pills-pool"
                            type="button"
                            role="tab">
                        <i class="fas fa-users me-2"></i> Pool Bonuses
                    </button>

                    <button class="nav-link"
                            id="v-pills-rank-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#v-pills-rank"
                            type="button"
                            role="tab">
                        <i class="fas fa-award me-2"></i> Rank Distribution
                    </button>

                    <button class="nav-link"
                            id="v-pills-club-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#v-pills-club"
                            type="button"
                            role="tab">
                        <i class="fas fa-star me-2"></i> Club Distribution
                    </button>

                    <button class="nav-link"
                            id="v-pills-other-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#v-pills-other"
                            type="button"
                            role="tab">
                        <i class="fas fa-cog me-2"></i> Reactivation Settings
                    </button>

                </div>
            </div>

            <!-- Right Content Area -->
            <div class="col-md-8">
                <div class="tab-content" id="v-pills-tabContent">

                    <!-- ================= LEVEL BONUS TAB ================= -->
                    <div class="tab-pane fade show active"
                         id="v-pills-level"
                         role="tabpanel">

                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">
                                    Level Bonuses (%)
                                    <i class="fas fa-info-circle text-muted ms-1"
                                       data-bs-toggle="tooltip"
                                       title="Set bonus percentage and minimum direct referral user shares buy for each level">
                                    </i>
                                </h5>

                                @foreach([1,2,3,4,5] as $i)
                                <div class="row border rounded p-3 mb-3">

                                    <h6 class="text-primary mb-3">
                                        Level {{ $i }}
                                    </h6>

                                    <!-- BONUS -->
                                    <div class="col-md-6 mb-3">
                                        <label>Bonus (%)</label>
                                        <input type="number"
                                               step="0.01"
                                               name="level{{ $i }}"
                                               value="{{ $bonus->{'level'.$i} }}"
                                               class="form-control">
                                    </div>

                                    <!-- MIN SHARES -->
                                    <div class="col-md-6 mb-3">
                                        <label>
                                            Min Shares Buy
                                            <i class="fas fa-info-circle text-muted ms-1"
                                               data-bs-toggle="tooltip"
                                               title="0 means no minimum share requirement">
                                            </i>
                                        </label>
                                        <input type="number"
                                               min="0"
                                               name="level{{ $i }}_min_shares"
                                               value="{{ $bonus->{'level'.$i.'_min_shares'} }}"
                                               class="form-control">
                                    </div>

                                </div>
                                @endforeach

                            </div>
                        </div>
                    </div>

                    <!-- ================= POOL BONUS TAB ================= -->
                    <div class="tab-pane fade"
                         id="v-pills-pool"
                         role="tabpanel">

                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Pool Bonuses (%)
                                    <i class="fas fa-info-circle text-muted ms-1"
                                       data-bs-toggle="tooltip"
                                       title="Set the % that will be deposited into the admin pool for distributing these bonuses.">
                                    </i>
                                </h5>

                                <div class="row">
                                    @foreach (['rank_pool','club_pool','shareholder_pool','director_pool'] as $pool)
                                        <div class="col-md-6 mb-3">
                                            <label>{{ ucwords(str_replace('_',' ',$pool)) }} (%)</label>
                                            <input type="number"
                                                   step="0.01"
                                                   name="{{ $pool }}"
                                                   value="{{ $bonus->$pool }}"
                                                   class="form-control">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================= RANK TAB ================= -->
                    <div class="tab-pane fade"
                         id="v-pills-rank"
                         role="tabpanel">

                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Rank Distribution (%)
                                    <i class="fas fa-info-circle text-muted ms-1"
                                       data-bs-toggle="tooltip"
                                       title="Set the Rank Bonus for User.">
                                    </i>
                                </h5>

                                <div class="row">
                                    @foreach (['rank1_percent','rank2_percent','rank3_percent'] as $rank)
                                        <div class="col-md-6 mb-3">
                                            <label>{{ ucwords(str_replace('_',' ',$rank)) }} (%)</label>
                                            <input type="number"
                                                   step="0.01"
                                                   name="{{ $rank }}"
                                                   value="{{ $bonus->$rank }}"
                                                   class="form-control">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================= CLUB TAB ================= -->
                    <div class="tab-pane fade"
                         id="v-pills-club"
                         role="tabpanel">

                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Club Distribution (%)
                                    <i class="fas fa-info-circle text-muted ms-1"
                                       data-bs-toggle="tooltip"
                                       title="Set the Club Bonus for User.">
                                    </i>
                                </h5>

                                <div class="row">
                                    @foreach (['club1_percent','club2_percent','club3_percent'] as $club)
                                        <div class="col-md-6 mb-3">
                                            <label>{{ ucwords(str_replace('_',' ',$club)) }} (%)</label>
                                            <input type="number"
                                                   step="0.01"
                                                   name="{{ $club }}"
                                                   value="{{ $bonus->$club }}"
                                                   class="form-control">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================= OTHER TAB ================= -->
                    <div class="tab-pane fade"
                         id="v-pills-other"
                         role="tabpanel">

                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Reactivation Settings
                                    <i class="fas fa-info-circle text-muted ms-1"
                                       data-bs-toggle="tooltip"
                                       title="If a share is inactive due to unpaid invoices, clear the pending invoices and show the reactivation charge.">
                                    </i>
                                </h5>

                                <div class="mb-3">
                                    <label>Reactivation Charge (৳)</label>
                                    <input type="number"
                                           step="0.01"
                                           name="reactivation_charge"
                                           value="{{ number_format($bonus->reactivation_charge, 2) }}"
                                           class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label>Max Pending Installments</label>
                                    <input type="number"
                                           min="1"
                                           name="max_pending_installments"
                                           value="{{ number_format($bonus->max_pending_installments, 2) }}"
                                           class="form-control">
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <!-- Submit Button -->
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        Update Settings
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<style>
.nav-pills .nav-link {
    border-radius: 0.25rem;
    margin-bottom: 0.5rem;
    padding: 0.75rem 1rem;
    font-weight: 500;
}
.nav-pills .nav-link.active {
    background-color: #0d6efd;
    color: #fff;
}
.card {
    border: none;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.card-title {
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 20px;
    font-weight: 600;
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
