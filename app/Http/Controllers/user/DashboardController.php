<?php

namespace App\Http\Controllers\user;


use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Investor;
use App\Models\Invoice;
use App\Models\Transactions;
use App\Models\User;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class DashboardController extends Controller
{
    public function index() : view
    {
        $user = auth()->user();

        $totalDeposit = Deposit::where('user_id', $user->id)->where('status', 'approved')->sum('amount');
        $totalWithdraw = Withdrawal::where('user_id', $user->id)->sum('amount');
        $totalTransfer = Transactions::where('user_id', $user->id)->where('remark', 'transfer')->sum('amount');
        $bonusBalance = $user->bonus_wallet;

        // $bonusBalance = Transactions::where('user_id', $user->id) ->where('type', '+')->whereIn('remark', ['rank_bonus', 'director_bonus', 'club_bonus', 'shareholder_bonus'])->sum('amount');


        $activeReferrals = User::where('refer_by', $user->id)->where('is_active', 1)->latest()->take(4)->get();
        $inactiveReferrals = User::where('refer_by', $user->id)->where('is_active', 0)->latest()->take(4)->get();

        $allowedTypes = [ 'transfer', 'convert', 'level_bonus', 'director_bonus', 'shareholder_bonus', 'club_bonus', 'rank_bonus', ];

            $allowedTransactionRemarks = [
                'withdrawal',
                'transfer',
                'convert',
                'level_bonus',
                'director_bonus',
                'shareholder_bonus',
                'club_bonus',
                'rank_bonus',
            ];

            // Transactions table data
            $transactions = Transactions::where('user_id', $user->id)
                ->whereIn('remark', $allowedTransactionRemarks)
                ->select('id', 'remark', 'amount', 'details', 'created_at')
                ->get()
                ->map(function ($item) {
                    $item->source = 'transaction';
                    return $item;
                });

            // Deposit table data
                $deposits = Deposit::with('method')
                    ->where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->select('id', 'method_id', 'amount', 'note', 'created_at')
                    ->get()
                    ->map(function ($item) {

                        $methodName = $item->method->name ?? 'Unknown Method';

                        $item->remark  = 'deposit';
                        $item->details = 'Deposit Via ' . $methodName;
                        $item->source  = 'deposit';

                        return $item;
                    });

                // Invoice Payments (Paid)
                $invoices = Invoice::with('investor.package')->where('user_id', $user->id)->where('status', 'paid')->select('id', 'invoice_no', 'investor_id', 'amount', 'created_at')->get()->map(function ($item) {

                    $item->remark  = 'invoice_payment';
                    $item->details = 'Invoice #' . $item->invoice_no . ' paid for share ' .($item->investor->package->share_name);
                    $item->source  = 'invoice';

                    return $item;
                });

                // Withdrawal

                $withdrawal = Withdrawal::where('user_id', $user->id)->select('id','method', 'details', 'amount', 'created_at', 'note')->get()->map(function ($item) {

                        $details = $item->details ?? [];

                        if (is_string($details)) {
                            $details = json_decode($details, true) ?? [];
                        }

                        $formattedDetails = collect($details)
                            ->filter(fn($v) => !empty($v))
                            ->map(function ($value, $key) {
                                return ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
                            })
                            ->implode(' | ');

                        $item->remark  = 'withdrawal';
                        $item->details = 'Withdraw via ' . ucfirst(strtolower($item->method ?? 'Unknown Method')) . ' | ' . ($formattedDetails ?: 'No details');
                        $item->source  = 'withdrawal';

                        return $item;
                    });

            // Merge + sort + limit
            $allTransactions = $transactions
                ->merge($deposits)
                ->merge($invoices)
                ->merge($withdrawal)
                ->sortByDesc('created_at')
                ->take(6)
                ->values();


        $now = Carbon::now();
            $lastMonth = $now->copy()->subMonth();

            $totalInvestment = Investor::where('user_id', $user->id)->sum('total_amount');

            $startOfMonth = $now->copy()->startOfMonth();
            // $previousTotalInvestment = Investor::where('user_id', $user->id)
            //     ->where('start_date', '<', $startOfMonth)
            //     ->sum('amount');
            // $totalInvestmentChange = $this->calculatePercentageChange($totalInvestment, $previousTotalInvestment);

            // $totalInvestmentChangeFormatted = ($totalInvestmentChange >= 0 ? '+' : '') . number_format($totalInvestmentChange, 2) . '%';

            // $totalInvestmentSinceLastMonth = $totalInvestmentChangeFormatted;

            // $runningInvestment = Investor::where('user_id', $user->id)
            //     ->where('status', 'running')
            //     ->sum('amount');
            // $previousRunningInvestment = Investor::where('user_id', $user->id)
            //     ->where('status', 'running')
            //     ->where('start_date', '<', $startOfMonth)
            //     ->sum('amount');

            // $runningInvestmentChange = $this->calculatePercentageChange($runningInvestment, $previousRunningInvestment);
            // $runningInvestmentChangeFormatted = ($runningInvestmentChange >= 0 ? '+' : '') . number_format($runningInvestmentChange, 2) . '%';
            // $maturedInvestment = Investor::where('user_id', $user->id)
            //     ->where('status', 'completed')
            //     ->sum('amount');
            // $previousMaturedInvestment = Investor::where('user_id', $user->id)
            //     ->where('status', 'completed')
            //     ->where('start_date', '<', $startOfMonth)
            //     ->sum('amount');

            // $maturedInvestmentChange = $this->calculatePercentageChange($maturedInvestment, $previousMaturedInvestment);
            // $maturedInvestmentChangeFormatted = ($maturedInvestmentChange >= 0 ? '+' : '') . number_format($maturedInvestmentChange, 2) . '%';


            $lastWithdraw = Withdrawal::where('user_id', $user->id)->orderBy('created_at', 'desc') ->first();
            $lastTransfer = Transactions::where('user_id', $user->id) ->where('remark', 'transfer')->orderBy('created_at', 'desc')->first();
            $lastDeposit = Deposit::where('user_id', $user->id)->where('status', 'approved')->orderBy('created_at', 'desc')->first();

            $startDate = now()->subDays(30)->startOfDay();

            $depositsData = Deposit::where('user_id', $user->id)->where('status', true)->where('created_at', '>=', $startDate)->selectRaw('DATE(created_at) as date, SUM(amount) as total')->groupBy('date')->orderBy('date')->get();

            $transfersData = Transactions::where('user_id', $user->id)->where('remark', 'transfer')->where('created_at', '>=', $startDate)->selectRaw('DATE(created_at) as date, SUM(amount) as total')->groupBy('date')->orderBy('date')->get();

            $withdrawsData = Withdrawal::where('user_id', $user->id)->where('created_at', '>=', $startDate)->selectRaw('DATE(created_at) as date, SUM(amount) as total')->groupBy('date')->orderBy('date')->get();

            // $totalExpectedReturn = Investor::where('user_id', $user->id)->where('status', 'running')->sum('expected_return');

            $totalSharesBought = Investor::where('user_id', $user->id)->sum('quantity');
            $totalInstallmentShares = Investor::where('user_id', $user->id)->where('purchase_type', 'installment')->sum('quantity');

            $dates = collect(range(0, 29))->map(function ($days) use ($startDate)
            { return $startDate->copy()->addDays($days)->format('Y-m-d');
            })->toArray();

            $depositMap = $depositsData->pluck('total', 'date')->toArray();
            $transferMap = $transfersData->pluck('total', 'date')->toArray();
            $withdrawMap = $withdrawsData->pluck('total', 'date')->toArray();

            $depositSeries = [];
            $transferSeries = [];
            $withdrawSeries = [];

            foreach ($dates as $date) {
                $depositSeries[] = $depositMap[$date] ?? 0;
                $transferSeries[] = $transferMap[$date] ?? 0;
                $withdrawSeries[] = $withdrawMap[$date] ?? 0;
            }

            $rankLabels = [
                'none'  => 'No Rank',
                'rank1' => 'Land Pioneer',
                'rank2' => 'Land Baron',
                'rank3' => 'Land Magnate',
            ];

            // $rankColors = [
            //     'none'  => 'danger',
            //     'rank1' => 'secondary',
            //     'rank2' => 'info',
            //     'rank3' => 'success',
            // ];

            $currentRank = $user->rank ?? 'none';

            $clubLabels = [
                'none'  => 'No Club',
                'club1' => 'Greenfield Club',
                'club2' => 'Prime Land Club',
                'club3' => 'Elite Land Owners Club',
            ];

            // $rankColors = [
            //     'none'  => 'danger',
            //     'rank1' => 'secondary',
            //     'rank2' => 'info',
            //     'rank3' => 'success',
            // ];

            $currentClub = $user->club ?? 'none';

        $dashboard = [
            'totalDeposit' => $totalDeposit,
            'totalWithdraw'   => $totalWithdraw,
            'totalTransfer'   => $totalTransfer,
            'bonusBalance'  => $bonusBalance,
            'totalInvestment' => $totalInvestment,
            // 'runningInvestment' => $runningInvestment,
            // 'maturedInvestment' => $maturedInvestment,
            // 'totalInvestmentChange' => $totalInvestmentChangeFormatted,
            // 'runningInvestmentChange' => $runningInvestmentChangeFormatted,
            // 'maturedInvestmentChange' => $maturedInvestmentChangeFormatted,
            // 'totalInvestmentSinceLastMonth' => $totalInvestmentChangeFormatted,
            // 'runningInvestmentSinceLastMonth' => $runningInvestmentChangeFormatted,
            // 'maturedInvestmentSinceLastMonth' => $maturedInvestmentChangeFormatted,
            'lastWithdraw' => $lastWithdraw,
            'lastTransfer' => $lastTransfer,
            'lastDeposit' => $lastDeposit,
            'chartDates' => $dates,
            'chartDeposits' => $depositSeries,
            'chartTransfers' => $transferSeries,
            'chartWithdraws' => $withdrawSeries,
            'transactions' => $allTransactions,
            'activeReferrals' => $activeReferrals,
            'inactiveReferrals' => $inactiveReferrals,
            // 'totalExpectedReturn' => $totalExpectedReturn,
            'totalSharesBought' => $totalSharesBought,
            'totalInstallmentShares' => $totalInstallmentShares,
            'rank_label'  => $rankLabels[$currentRank] ?? 'No Rank',
            // 'rank_color'  => $rankColors[$currentRank] ?? 'danger',
            'club_label'  => $clubLabels[$currentClub] ?? 'No Club',

         ];

        return view ('user.pages.dashboard', compact('user', 'dashboard' ));
    }
    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return (($current - $previous) / $previous) * 100;
    }
}
