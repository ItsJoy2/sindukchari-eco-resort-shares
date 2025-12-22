<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Investor;
use App\Models\PoolWallet;
use Illuminate\Support\Str;
use App\Models\BonusSetting;
use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Models\ReactivationLog;
use Illuminate\Support\Facades\DB;
use App\Service\TransactionService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PurchaseController extends Controller
{
    // Show all share packages
    public function index()
    {
        $user = auth()->user();

        $packages = Package::where('status', 'active')
            ->orderBy('id', 'ASC')
            ->get()
            ->map(function ($package) use ($user) {

                // User purchased shares (user-wise)
                $package->user_purchased = Investor::where('user_id', $user->id)
                    ->where('package_id', $package->id)
                    ->sum('quantity');

                // User remaining limit
                $package->user_remaining =
                    $package->per_purchase_limit - $package->user_purchased;

                if ($package->user_remaining < 0) {
                    $package->user_remaining = 0;
                }

                // Total sold shares for this package
                $totalSold = Investor::where('package_id', $package->id)
                    ->sum('quantity');

                // Remaining shares for this package
                $package->remaining_shares =
                    $package->total_share_quantity - $totalSold;

                if ($package->remaining_shares < 0) {
                    $package->remaining_shares = 0;
                }

                return $package;
            });

        return view('user.pages.package.index', compact('packages'));
    }



    // Purchase function
    public function purchase(Request $request)
    {
        $request->validate([
            'package_id'     => 'required|exists:packages,id',
            'purchase_type'  => 'required|in:full,installment',
            'quantity'       => 'required|integer|min:1',
        ]);

        $user     = auth()->user();
        $package  = Package::findOrFail($request->package_id);
        $quantity = (int) $request->quantity;

        // Per user purchase limit
        $alreadyPurchased = Investor::where('user_id', $user->id)
            ->where('package_id', $package->id)
            ->sum('quantity');

        if (($alreadyPurchased + $quantity) > $package->per_purchase_limit) {
            return back()->with('error', 'You have reached the purchase limit for this package.');
        }

        // ---------------- AMOUNT CALCULATION ----------------
        $baseAmount = $package->amount * $quantity;

        // Discount ONLY for full payment (percent-based)
        $discountPercent = $package->discount ?? 0;
        $totalDiscount = 0;

        if ($request->purchase_type === 'full' && $discountPercent > 0) {
            $totalDiscount = ($baseAmount * $discountPercent) / 100;
        }

        $totalPayable = $baseAmount - $totalDiscount;
        $firstInstallmentAmount = $package->first_installment * $quantity;

        // Wallet checks
        if (
            $request->purchase_type === 'full' &&
            $user->funding_wallet < $totalPayable
        ) {
            return back()->with('error', 'Insufficient balance for full payment.');
        }

        if (
            $request->purchase_type === 'installment' &&
            $user->funding_wallet < $firstInstallmentAmount
        ) {
            return back()->with('error', 'Insufficient balance for first installment.');
        }

        // Activate user if inactive
        if (!$user->is_active) {
            $user->is_active = true;
            $user->save();
        }

        return DB::transaction(function () use (
            $user,
            $package,
            $quantity,
            $request,
            $baseAmount,
            $totalDiscount,
            $totalPayable
        ) {

            // ---------------- INVESTOR CREATE ----------------
            $investor = Investor::create([
                'user_id'         => $user->id,
                'package_id'      => $package->id,
                'quantity'        => $quantity,
                'purchase_type'   => $request->purchase_type,
                'total_amount'    => $baseAmount,
                'discount' => $totalDiscount,
                'status'          => $request->purchase_type === 'full' ? 'paid' : 'active',
            ]);

            // ---------------- PAYMENT ----------------
            if ($request->purchase_type === 'full') {
                $this->fullPayment(
                    $user,
                    $investor,
                    $totalPayable,
                    $totalDiscount
                );
            } else {
                $this->firstInstallmentPayment($user, $package, $investor, $quantity);
            }

            // ---------------- USER RANK / CLUB ----------------
            $settings = DB::table('settings')->pluck('value', 'key')->toArray();
            $totalShares = Investor::where('user_id', $user->id)->sum('quantity');
            $activeReferrals = User::where('refer_by', $user->id)
                ->where('is_active', true)
                ->count();

            $user->is_shareholder = $totalShares >= ($settings['shareholder_min_shares'] ?? 5);

            if ($totalShares >= ($settings['club3_min_shares'] ?? 50)) {
                $user->club = 'club3';
            } elseif ($totalShares >= ($settings['club2_min_shares'] ?? 25)) {
                $user->club = 'club2';
            } elseif ($totalShares >= ($settings['club1_min_shares'] ?? 10)) {
                $user->club = 'club1';
            } else {
                $user->club = 'none';
            }

            if (
                $totalShares >= ($settings['rank3_min_shares'] ?? 50) &&
                $activeReferrals >= ($settings['rank3_min_active_referrals'] ?? 15)
            ) {
                $user->rank = 'rank3';
            } elseif (
                $totalShares >= ($settings['rank2_min_shares'] ?? 25) &&
                $activeReferrals >= ($settings['rank2_min_active_referrals'] ?? 10)
            ) {
                $user->rank = 'rank2';
            } elseif (
                $totalShares >= ($settings['rank1_min_shares'] ?? 10) &&
                $activeReferrals >= ($settings['rank1_min_active_referrals'] ?? 5)
            ) {
                $user->rank = 'rank1';
            } else {
                $user->rank = 'none';
            }

            $user->save();

            return back()->with('success', 'Share purchase successful!');
        });
    }

    /**
     * Full Payment
     */
    private function fullPayment($user, $investor, $payableAmount, $discount)
    {
        $user->funding_wallet -= $payableAmount;
        $user->save();

        $investor->paid_amount = $payableAmount;
        $investor->status = 'paid';
        $investor->save();

        Invoice::create([
            'invoice_no'      => 'INV-' . Str::upper(Str::random(6)),
            'user_id'         => $user->id,
            'investor_id'     => $investor->id,
            'amount'          => $payableAmount,
            'discount_amount' => $discount,
            'type'            => 'full',
            'status'          => 'paid',
        ]);

        // Bonus only on paid amount
        $this->levelBonus($user, $investor, $payableAmount);
        $this->poolBonus($payableAmount);
    }


    /**
     * First Installment Payment
     */
    public function firstInstallmentPayment($user, $package, $investor, $quantity)
    {
        $firstInstallment = $package->first_installment * $quantity;

        if ($user->funding_wallet < $firstInstallment) {
            return back()->with('error', 'Insufficient balance for first installment.');
        }

        $user->funding_wallet -= $firstInstallment;
        $user->save();

        $investor->paid_amount = $firstInstallment;
        $investor->paid_installments = 1;
        $investor->save();

        Invoice::create([
            'invoice_no' => 'INV-' . Str::upper(Str::random(6)),
            'user_id' => $user->id,
            'investor_id' => $investor->id,
            'amount' => $firstInstallment,
            'type' => 'installment',
            'status' => 'paid'
        ]);

        // Bonuses
        $this->levelBonus($user, $investor, $firstInstallment);
        $this->poolBonus($firstInstallment);

        return back()->with('success', 'First installment paid. Installment active.');
    }


    // Level Bonus
    public function levelBonus($user, $investor, $amount)
    {
        $settings = BonusSetting::first();

        $levels = [
            1 => [
                'bonus' => $settings->level1,
                'min_shares' => $settings->level1_min_shares,
            ],
            2 => [
                'bonus' => $settings->level2,
                'min_shares' => $settings->level2_min_shares,
            ],
            3 => [
                'bonus' => $settings->level3,
                'min_shares' => $settings->level3_min_shares,
            ],
            4 => [
                'bonus' => $settings->level4,
                'min_shares' => $settings->level4_min_shares,
            ],
            5 => [
                'bonus' => $settings->level5,
                'min_shares' => $settings->level5_min_shares,
            ],
        ];

        $ref = $user->refer_by;
        $level = 1;
        $transactionService = new TransactionService();

        while ($ref && $level <= 5) {

            $upline = User::find($ref);
            if (!$upline || !$upline->is_active) {
                $ref = $upline->refer_by ?? null;
                $level++;
                continue;
            }

            $directReferralIds = User::where('refer_by', $upline->id)->pluck('id');

            $directReferralShares = Investor::whereIn('user_id', $directReferralIds)
                ->sum('quantity');

            $requiredShares = $levels[$level]['min_shares'];

            if (
                $directReferralShares == 0 ||
                $directReferralShares >= $requiredShares
            ) {
                $bonusAmount = ($amount * $levels[$level]['bonus']) / 100;

                if ($bonusAmount > 0) {
                    $upline->bonus_wallet += $bonusAmount;
                    $upline->save();

                    $transactionService->addNewTransaction(
                        $upline->id,
                        $bonusAmount,
                        'level_bonus',
                        '+',
                        "Level {$level} bonus from {$user->email}"
                    );
                }
            }

            $ref = $upline->refer_by;
            $level++;
        }
    }

    // Pool Bonus
    public function poolBonus($amount)
    {
        $settings = BonusSetting::first();
        $pool = PoolWallet::first();

        // Rank Pool
        $rankAmount = ($amount * $settings->rank_pool) / 100;
        $pool->rank += $rankAmount;
        Transactions::create([
            'transaction_id' => Transactions::generateTransactionId(),
            'user_id' => 1,
            'amount' => $rankAmount,
            'remark' => "rank_pool",
            'type' => '+',
            'status' => 'Completed',
            'details' => "Added to Rank Pool",
            'charge' => 0,
        ]);

        // Club Pool
        $clubAmount = ($amount * $settings->club_pool) / 100;
        $pool->club += $clubAmount;
        Transactions::create([
            'transaction_id' => Transactions::generateTransactionId(),
            'user_id' => 1,
            'amount' => $clubAmount,
            'remark' => "club_pool",
            'type' => '+',
            'status' => 'Completed',
            'details' => "Added to Club Pool",
            'charge' => 0,
        ]);

        // Shareholder Pool
        $shareholderAmount = ($amount * $settings->shareholder_pool) / 100;
        $pool->shareholder += $shareholderAmount;
        Transactions::create([
            'transaction_id' => Transactions::generateTransactionId(),
            'user_id' => 1,
            'amount' => $shareholderAmount,
            'remark' => "shareholder_pool",
            'type' => '+',
            'status' => 'Completed',
            'details' => "Added to Shareholder Pool",
            'charge' => 0,
        ]);

        // Director Pool
        $directorAmount = ($amount * $settings->director_pool) / 100;
        $pool->director += $directorAmount;
        Transactions::create([
            'transaction_id' => Transactions::generateTransactionId(),
            'user_id' => 1,
            'amount' => $directorAmount,
            'remark' => "director_pool",
            'type' => '+',
            'status' => 'Completed',
            'details' => "Added to Director Pool",
            'charge' => 0,
        ]);

        $pool->save();
    }

        // Show invoices
    public function showInvoice()
    {
        $invoices = Invoice::where('user_id', auth()->id())
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('user.pages.package.invoice', compact('invoices'));
    }
    // invoice pay
    public function payInvoice($id)
    {
        $invoice = Invoice::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $user = auth()->user();
        $investor = $invoice->investor;

        $settings = BonusSetting::first();
        $reactivationCharge = $settings->reactivation_charge;

        // Get all pending invoices for THIS investor
        $pendingInvoices = Invoice::where('investor_id', $investor->id)
            ->where('status', 'pending')
            ->get();

        $totalPendingAmount = $pendingInvoices->sum('amount');

        // Total payable
        $totalPaymentRequired = $totalPendingAmount;

        // If inactive → add reactivation charge
        if ($investor->status === 'inactive') {
            $totalPaymentRequired += $reactivationCharge;
        }

        // Wallet check
        if ($user->funding_wallet < $totalPaymentRequired) {
            return back()->with('error', 'Insufficient balance. You must pay all pending installments plus reactivation charge.');
        }

        DB::beginTransaction();
        try {

            // Deduct wallet balance
            $user->funding_wallet -= $totalPaymentRequired;
            $user->save();

            // If inactive → Reactivate and log
            if ($investor->status === 'inactive') {
                ReactivationLog::create([
                    'investor_id'   => $investor->id,
                    'user_id'       => $user->id,
                    'charge_amount' => $reactivationCharge,
                    'total_paid'    => $totalPaymentRequired,
                ]);

                $investor->status = 'active';
                $investor->save();
            }

            // Pay all pending invoices
            foreach ($pendingInvoices as $pending) {
                $pending->status = 'paid';
                $pending->save();

                // Update investor payments
                $investor->paid_installments += 1;
                $investor->paid_amount += $pending->amount;

                // ******** Generate Level Bonus ********
                $this->levelBonus($user, $investor, $pending->amount);

                // ******** Generate Pool Bonus ********
                $this->poolBonus($pending->amount);
            }

            // ------------------- Update Investor Status -------------------
            if ($investor->paid_amount >= $investor->total_amount) {
                $investor->status = 'paid';
            } elseif ($investor->status === 'inactive') {
                $investor->status = 'inactive';
            } else {
                $investor->status = 'active';
            }

            $investor->save();

            DB::commit();

            return back()->with('success','Invoices paid successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function payAnyAmount(Request $request, $investorId)
    {
        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:1000',
                function ($attribute, $value, $fail) {
                    if ($value % 1000 !== 0) {
                        $fail('Amount must be in multiples of 1000 (e.g. 1000, 2000, 3000).');
                    }
                },
            ],
        ]);

        $user = auth()->user();
        $investor = Investor::where('id', $investorId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $payAmount = (float) $request->amount;
        $settings = BonusSetting::first();
        $reactivationCharge = $settings->reactivation_charge;

        // Remaining amount + Reactivation (if inactive)
        $remaining = $investor->total_amount - $investor->paid_amount;
        if ($investor->status === 'inactive') {
            $remaining += $reactivationCharge;
        }

        if ($payAmount > $remaining) {
            return back()->with('error', 'You cannot pay more than remaining amount: '.$remaining);
        }

        DB::transaction(function () use ($user, $investor, &$payAmount, $reactivationCharge) {

            // 🔹 Wallet check (total needed)
            $totalRequired = $payAmount;
            if ($investor->status === 'inactive') {
                $totalRequired += $reactivationCharge;
            }
            if ($user->funding_wallet < $totalRequired) {
                throw new \Exception('Insufficient wallet balance.');
            }

            // Deduct total
            $user->funding_wallet -= $totalRequired;
            $user->save();

            // 🔹 Handle Reactivation fee
            if ($investor->status === 'inactive') {
                ReactivationLog::create([
                    'investor_id'   => $investor->id,
                    'user_id'       => $user->id,
                    'charge_amount' => $reactivationCharge,
                    'total_paid'    => $reactivationCharge,
                ]);

                $investor->status = 'active';
                $investor->save();

                $payAmount -= $reactivationCharge;
            }

            // Pay pending invoices first
            $pendingInvoices = Invoice::where('investor_id', $investor->id)
                ->where('status', 'pending')
                ->orderBy('id', 'ASC')
                ->get();

            foreach ($pendingInvoices as $invoice) {
                if ($payAmount <= 0) break;

                if ($payAmount < $invoice->amount) break;

                $invoice->status = 'paid';
                $invoice->save();

                $investor->paid_installments += 1;
                $investor->paid_amount += $invoice->amount;
                $investor->save();

                $this->levelBonus($user, $investor, $invoice->amount);
                $this->poolBonus($invoice->amount);

                $payAmount -= $invoice->amount;
            }

            // 🔹 Create advance invoice if any leftover
            if ($payAmount > 0) {
                Invoice::create([
                    'invoice_no' => 'INV-' . Str::upper(Str::random(6)),
                    'user_id' => $user->id,
                    'investor_id' => $investor->id,
                    'amount' => $payAmount,
                    'type' => 'installment',
                    'status' => 'paid'
                ]);

                $investor->paid_installments += 1;
                $investor->paid_amount += $payAmount;
                $investor->save();

                $this->levelBonus($user, $investor, $payAmount);
                $this->poolBonus($payAmount);
            }

            // 🔹 Update final status
            if ($investor->paid_amount >= $investor->total_amount) {
                $investor->status = 'paid';
                $investor->save();

                Invoice::where('investor_id', $investor->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'paid', 'updated_at' => now()]);
            } else {
                $investor->status = 'active';
                $investor->save();
            }

        });

        return back()->with('success', 'Payment completed successfully.');
    }

    public function myInvestments()
    {
        $investors = Investor::where('user_id', auth()->id())
            ->with('package')
            ->orderBy('id', 'DESC')
            ->get();

        return view('user.pages.package.my-investment', compact('investors'));
    }


}
