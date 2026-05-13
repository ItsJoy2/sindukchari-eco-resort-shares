<?php

namespace App\Http\Controllers\admin;

use App\Exports\AccountsExport;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\GeneralSetting;
use App\Models\Invoice;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;


class AccountsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $from = null;
        $to = null;

        if ($request->date_range) {
            $dates = explode(' to ', $request->date_range);

            $from = $dates[0] ?? null;
            $to   = $dates[1] ?? null;
        }
        $filter = $request->filter;
        $search = $request->search;

        //  MANUAL
        $accounts = Account::with('category')
            ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'date' => $item->date,
                    'type' => $item->type,
                    'amount' => $item->amount,
                    'category' => $item->category->name ?? '-',
                    'category_id' => $item->category_id,
                    'note' => $item->note,
                    'is_manual' => true,
                    'created_at' => $item->date,
                ];
            });

        //  INVOICE
        $invoices = Invoice::with(['user','investor.package'])
            ->where('status', 'paid')
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'invoice_id' => $item->id,
                    'date' => $item->created_at->format('Y-m-d'),
                    'type' => 'income',
                    'amount' => $item->amount,
                    'category' => 'Invoice Payment',
                    'note' => 'Paid for Share: '
                        . ($item->investor->package->share_name ?? 'N/A')
                        . ' by '
                        . ($item->user->email ?? 'N/A'),
                    'is_manual' => false,
                    'is_invoice' => true,
                    'created_at' => $item->created_at,
                ];
            });

        // WITHDRAWALS
        $withdrawals = Withdrawal::with('user')
            ->where('status', 'approved')
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->get()
            ->flatMap(function ($item) {

                $details = $item->details ?? [];

                $detailsText = collect($details)
                    ->filter(fn($v) => !empty($v))
                    ->map(function ($value, $key) {
                        return ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
                    })
                    ->implode(' | ');

                $note = 'Withdraw by '
                    . ($item->user->email ?? 'N/A'). ' via ' . ucfirst($item->method)
                    . ($detailsText ? ' | ' . $detailsText : '');

                 $chargeNote = 'Withdraw charge recieved from '
                    . ($item->user->email ?? 'N/A');

                return [

                    [
                        'id' => $item->id,
                        'date' => $item->created_at->format('Y-m-d'),
                        'type' => 'expense',
                        'amount' => $item->total_amount,
                        'category' => 'Withdraw',
                        'note' => $note,
                        'is_manual' => false,
                        'created_at' => $item->created_at,
                    ],

                    [
                        'id' => $item->id . '_charge',
                        'date' => $item->created_at->format('Y-m-d'),
                        'type' => 'income',
                        'amount' => $item->charge,
                        'category' => 'Withdraw Charge',
                        'note' => $chargeNote,
                        'is_manual' => false,
                        'created_at' => $item->created_at,
                    ]
                ];
            });

        //  MERGE
        $all = collect()
            ->merge($accounts)
            ->merge($invoices)
            ->merge($withdrawals);

        //  FILTER
        $all = $all->when($filter, function ($collection) use ($filter) {

            // type filter
            if (in_array($filter, ['income', 'expense'])) {
                return $collection->where('type', $filter);
            }

            // category filter
            if (str_starts_with($filter, 'cat_')) {
                $cat = str_replace('cat_', '', $filter);
                return $collection->where('category', $cat);
            }

            return $collection;
        });

        //  SEARCH
        $all = $all->when($search, function ($collection) use ($search) {
            return $collection->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['title']), strtolower($search)) ||
                    str_contains(strtolower($item['note']), strtolower($search)) ||
                    str_contains(strtolower($item['category']), strtolower($search)) ||
                    str_contains((string)$item['amount'], $search);
            });
        });

        //  SORT
        // $all = $all->sortByDesc('date')->values();
        $all = $all->sortByDesc(function ($item) {return Carbon::parse($item['created_at'] ?? $item['date'])->timestamp;})->values();

        //  PAGINATION
        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $currentItems = $all->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $accountsData = new LengthAwarePaginator(
            $currentItems,
            $all->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );


        // total income (manual + invoice)
        $totalIncome = $all->where('type', 'income')->sum('amount');

        // total expense
        $totalExpense = $all->where('type', 'expense')->sum('amount');

        // invoice only
        $totalInvoice = $all
            ->where('category', 'Invoice Payment')
            ->sum('amount');

        // additional income
        // $additionalIncome = $all
        //     ->where('type', 'income')
        //     ->where('category', '!=', 'Invoice Payment')
        //     ->sum('amount');

        // net profit
        $netProfit = $totalIncome - $totalExpense;

        $categories = AccountCategory::where('status', 1)->get();

        return view('admin.pages.accounts.index', compact('accountsData', 'totalIncome', 'totalExpense', 'totalInvoice', 'netProfit', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = AccountCategory::where('status', 1)->get();
        return view('admin.pages.accounts.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:account_categories,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable'
        ]);

        Account::create([
            'type' => $request->type,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'note' => $request->note,
        ]);

        return redirect()->route('admin.accounts.index')->with('success', ucfirst($request->type) . ' Added Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $account = Account::findOrFail($id);
        $categories = AccountCategory::where('status', 1)->get();

        return view('admin.pages.accounts.edit', compact('account', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:account_categories,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable'
        ]);

        $account = Account::findOrFail($id);

        $account->update([
            'type' => $request->type,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'note' => $request->note,
        ]);

        return redirect()->route('admin.accounts.index')->with('success', ucfirst($request->type) . ' Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $account = Account::findOrFail($id);
        $account->delete();

        return back()->with('success', 'Account Deleted Successfully');
    }

    /**
     * Export accounts data as CSV, Excel or PDF
     */
    public function export(Request $request, $type)
    {
        $data = $this->getFilteredData($request);

        //  CSV
        if ($type == 'csv') {

            $now = now()->format('Y-m-d_H-i');
            $fileName = "accounts_{$now}.csv";

            $data = $this->getFilteredData($request);

            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
            ];

            $callback = function() use ($data, $request) {

                $file = fopen('php://output', 'w');

                // ===== TOP INFO =====
                fputcsv($file, ['Accounts Report']);
                fputcsv($file, ['Date Range:', $request->date_range ?? 'All']);
                fputcsv($file, ['Filter:', $this->formatFilterName($request->filter)]);
                fputcsv($file, ['Search:', $request->search ?? '-']);
                fputcsv($file, []);

                // ===== HEADER =====
                fputcsv($file, ['Date','Type','Category','Amount','Note']);

                // ===== DATA =====
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row['date'],
                        $row['type'],
                        $row['category'],
                        $row['amount'],
                        $row['note'],
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        if ($type == 'excel') {

            $now = now()->format('Y-m-d_H-i');

            return Excel::download(
                new AccountsExport($data, $request),
                "accounts_{$now}.xlsx"
            );
        }

        if ($type == 'pdf') {

            $now = now()->format('Y-m-d_H-i');

            $setting = GeneralSetting::first();

            $html = view('admin.pages.accounts.pdf', [
                'accounts' => $data,
                'date_range' => $request->date_range,
                'filter' => $this->formatFilterName($request->filter),
                'search' => $request->search,
                'app_name' => $setting->app_name ?? 'Edulife',
            ])->render();

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
            ]);

            $mpdf->WriteHTML($html);

            return response($mpdf->Output("accounts_{$now}.pdf", 'S'), 200)
                ->header('Content-Type', 'application/pdf');
        }

        return back();
    }

    /**
     * Get filtered data based on request parameters for export
     */
    private function getFilteredData($request)
    {
        $from   = $request->from_date;
        $to     = $request->to_date;
        $filter = $request->filter;
        $search = $request->search;

        // MANUAL
        $accounts = Account::with('category')
            ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'type' => $item->type,
                    'amount' => $item->amount,
                    'category' => $item->category->name ?? '-',
                    'note' => $item->note,
                ];
            });

        // INVOICE
        $invoices = Invoice::with(['user','investor.package'])
            ->where('status', 'paid')
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->created_at->format('Y-m-d'),
                    'type' => 'income',
                    'amount' => $item->amount,
                    'category' => 'Invoice Payment',
                    'note' => 'Paid for Share: '
                        . ($item->investor->package->share_name ?? 'N/A')
                        . ' by '
                        . ($item->user->email ?? 'N/A'),
                ];
            });

        // WITHDRAWALS
        $withdrawals = Withdrawal::with('user')
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->get()
            ->flatMap(function ($item) {

                $details = $item->details ?? [];

                $detailsText = collect($details)
                    ->filter(fn($v) => !empty($v))
                    ->map(function ($value, $key) {
                        return ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
                    })
                    ->implode(' | ');

                $note = 'Withdraw by '
                    . ($item->user->email ?? 'N/A')
                    . ($detailsText ? ' | ' . $detailsText : '');
                 $chargeNote = 'Withdraw charge recieved from '
                    . ($item->user->email ?? 'N/A');

                return [
                    [
                        'date' => $item->created_at->format('Y-m-d'),
                        'type' => 'expense',
                        'amount' => $item->total_amount,
                        'category' => 'Withdraw',
                        'note' => $note,
                    ],
                    [
                        'date' => $item->created_at->format('Y-m-d'),
                        'type' => 'income',
                        'amount' => $item->charge,
                        'category' => 'Withdraw Charge',
                        'note' => $chargeNote,
                    ]
                ];
            });

        // MERGE
        $all = collect()
            ->merge($accounts)
            ->merge($invoices)
            ->merge($withdrawals);

        // FILTER
        if ($filter) {

            if (in_array($filter, ['income', 'expense'])) {
                $all = $all->where('type', $filter);
            }

            if (str_starts_with($filter, 'cat_')) {

                $cat = str_replace('cat_', '', $filter);

                $catName = ucwords(str_replace('_', ' ', $cat));

                $all = $all->where('category', $catName);
            }
        }

        // SEARCH
        if ($search) {
            $all = $all->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['note']), strtolower($search)) ||
                    str_contains(strtolower($item['category']), strtolower($search)) ||
                    str_contains((string)$item['amount'], $search);
            });
        }

        return $all->sortByDesc('date')->values();
    }

    // Format filter name for display
    private function formatFilterName($filter)
    {
        if (!$filter) return 'All';

        if (in_array($filter, ['income', 'expense'])) {
            return ucfirst($filter);
        }

        if (str_starts_with($filter, 'cat_')) {
            $cat = str_replace('cat_', '', $filter);
            return ucwords(str_replace('_', ' ', $cat));
        }

        return ucfirst($filter);
    }
}
