@extends('user.layouts.app')

@section('userContent')
<div class="page-header">
    <h3 class="page-title">Buy Shares</h3>
</div>

@include('user.layouts.alert')

<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="card-body">
            <div class="row justify-content-center g-4">

                @foreach($packages as $package)
                <div class="col-md-6 col-lg-4 d-flex">
                    <div class="package-card w-100">

                        <div class="package-title">{{ $package->share_name }}</div>

                        <div class="price-tag">
                            ৳{{ number_format($package->amount, 2) }} <br>
                            @if($package->installment_months > 0)
                                <span style="font-size:12px;">(Installment Available)</span>
                            @endif
                        </div>

                        <hr>

                        <div class="stats-container mt-4">

                            <div class="stat-item">
                                <span>Total Shares</span>
                                <span>{{ $package->total_share_quantity }}</span>
                            </div>

                            <div class="stat-item">
                                <span>Remaining Shares</span>
                                <span>{{ $package->remaining_shares }}</span>
                            </div>

                            <div class="stat-item">
                                <span>Max Purchase</span>
                                <span>{{ $package->per_purchase_limit }} Shares</span>
                            </div>

                        </div>

                        <button type="button"
                            class="btn btn-purchase w-100 buyShareBtn mt-3"
                            data-id="{{ $package->id }}"
                            data-name="{{ $package->share_name }}"
                            data-price="{{ $package->amount }}"
                            data-limit="{{ $package->per_purchase_limit }}"
                            data-first="{{ $package->first_installment }}"
                            data-monthly="{{ $package->monthly_installment }}"
                            data-months="{{ $package->installment_months }}"
                            data-remaining="{{ $package->user_remaining }}"
                            data-discount="{{ $package->discount ?? 0 }}"> {{-- discount now % --}}
                            Buy Now
                        </button>

                    </div>
                </div>
                @endforeach

            </div>
        </div>
    </div>
</div>

{{-- ================= BUY SHARE MODAL ================= --}}
<div class="modal fade" id="buyShareModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('user.share.purchase') }}">
            @csrf
            <input type="hidden" name="package_id" id="modalPackageId">

            <div class="modal-content m-0">
                <div class="modal-header">
                    <h5 class="modal-title">Buy <span id="modalShareName"></span> Share</h5>
                    <button type="button" class="btn-close text-danger" data-bs-dismiss="modal">X</button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label>Quantity</label>
                        <input type="number"
                               min="1"
                               class="form-control"
                               name="quantity"
                               id="modalQuantityInput"
                               value="1"
                               required>
                        <small id="modalQuantityLimit" class="text-muted"></small>
                    </div>

                    <div class="mb-3">
                        <label>Payment Type</label>
                        <select name="purchase_type" id="paymentType" class="form-control text-white">
                            <option value="full">Full Payment</option>
                            <option value="installment">Installment</option>
                        </select>
                    </div>

                    {{-- Installment details --}}
                    <div id="installmentDetails" class="d-none">
                        <p><strong>First Installment:</strong> ৳<span id="firstInstallmentText">0.00</span></p>
                        <p><strong>Monthly EMI:</strong> ৳<span id="monthlyInstallmentText">0.00</span></p>
                        <p><strong>Duration:</strong> <span id="totalMonthsText">0</span> Months</p>
                    </div>

                    {{-- Discount (FULL payment only) --}}
                    <p id="discountRow" class="d-none text-success">
                        <strong>Discount ({{'%'}}):</strong> ৳<span id="discountAmountText">0.00</span>
                    </p>

                    <p class="mt-3">
                        <strong>Total Payable:</strong>
                        ৳<span id="modalTotalAmount">0.00</span>
                    </p>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">
                        Confirm Purchase
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

<script>
document.addEventListener("DOMContentLoaded", function () {

    const modal = new bootstrap.Modal(document.getElementById('buyShareModal'));

    const qtyInput = document.getElementById("modalQuantityInput");
    const paymentType = document.getElementById("paymentType");

    const installmentDetails = document.getElementById("installmentDetails");
    const discountRow = document.getElementById("discountRow");

    const modalTotalAmount = document.getElementById("modalTotalAmount");
    const discountAmountText = document.getElementById("discountAmountText");

    const firstInstallmentText = document.getElementById("firstInstallmentText");
    const monthlyInstallmentText = document.getElementById("monthlyInstallmentText");
    const totalMonthsText = document.getElementById("totalMonthsText");

    document.querySelectorAll(".buyShareBtn").forEach(btn => {

        btn.addEventListener("click", function () {

            let price = parseFloat(this.dataset.price);
            let discountPercent = parseFloat(this.dataset.discount); // now %
            let remaining = parseInt(this.dataset.remaining);
            let limit = parseInt(this.dataset.limit);
            let first = parseFloat(this.dataset.first);
            let monthly = parseFloat(this.dataset.monthly);
            let months = parseInt(this.dataset.months);

            document.getElementById("modalPackageId").value = this.dataset.id;
            document.getElementById("modalShareName").textContent = this.dataset.name;

            document.getElementById("modalQuantityLimit").textContent =
                `Max ${limit} shares — Remaining ${remaining}`;

            qtyInput.value = 1;
            qtyInput.max = remaining;

            totalMonthsText.textContent = months;
            installmentDetails.classList.add("d-none");
            discountRow.classList.add("d-none");

            calculateTotal();

            modal.show();

            qtyInput.oninput = calculateTotal;
            paymentType.onchange = calculateTotal;

            function calculateTotal() {
                let qty = parseInt(qtyInput.value || 1);
                if (qty > remaining) qty = remaining;

                let total = qty * price;
                let discountAmount = 0;

                if (paymentType.value === 'full' && discountPercent > 0) {
                    discountAmount = total * (discountPercent / 100);
                    total -= discountAmount;
                    discountRow.classList.remove("d-none");
                    discountAmountText.textContent = discountAmount.toFixed(2);
                } else {
                    discountRow.classList.add("d-none");
                    discountAmountText.textContent = "0.00";
                }

                modalTotalAmount.textContent = total.toFixed(2);

                if (paymentType.value === 'installment') {
                    installmentDetails.classList.remove("d-none");
                    firstInstallmentText.textContent = (first * qty).toFixed(2);
                    monthlyInstallmentText.textContent = (monthly * qty).toFixed(2);
                } else {
                    installmentDetails.classList.add("d-none");
                }
            }
        });
    });
});
</script>
