<?php

namespace App\Http\Controllers\admin;

use App\Exports\AccountsExport;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\GeneralSetting;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
                    'title' => $item->title,
                    'type' => $item->type,
                    'amount' => $item->amount,
                    'category' => $item->category->name ?? '-',
                    'note' => $item->note,
                    'is_manual' => true,
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
                    'date' => $item->created_at->format('Y-m-d'),
                    'title' => 'Invoice Payment',
                    'type' => 'income',
                    'amount' => $item->amount,
                    'category' => 'Invoice',
                    'note' => 'Paid for Share: '
                        . ($item->investor->package->share_name ?? 'N/A')
                        . ' by '
                        . ($item->user->email ?? 'N/A'),
                    'is_manual' => false,
                ];
            });

        //  MERGE
        $all = collect()
            ->merge($accounts)
            ->merge($invoices);

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
        $all = $all->sortByDesc('date')->values();

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
            ->where('category', 'Invoice')
            ->sum('amount');

        // additional income
        $additionalIncome = $all
            ->where('type', 'income')
            ->where('category', '!=', 'Invoice')
            ->sum('amount');

        return view('admin.pages.accounts.index', compact('accountsData', 'totalIncome', 'totalExpense', 'totalInvoice', 'additionalIncome'));
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
            'title' => 'required',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:account_categories,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable'
        ]);

        Account::create([
            'title' => $request->title,
            'type' => $request->type,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'note' => $request->note,
        ]);

        return redirect()->route('admin.accounts.index')->with('success', 'Account Added Successfully');
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
            'title' => 'required',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:account_categories,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable'
        ]);

        $account = Account::findOrFail($id);

        $account->update([
            'title' => $request->title,
            'type' => $request->type,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'note' => $request->note,
        ]);

        return redirect()->route('admin.accounts.index')
            ->with('success', 'Account Updated Successfully');
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
                fputcsv($file, ['Filter:', $request->filter ?? 'All']);
                fputcsv($file, ['Search:', $request->search ?? '-']);
                fputcsv($file, []);

                // ===== HEADER =====
                fputcsv($file, ['Date','Title','Type','Category','Amount','Note']);

                // ===== DATA =====
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row['date'],
                        $row['title'],
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
                new AccountsExport($data),
                "accounts_{$now}.xlsx"
            );
        }

        if ($type == 'pdf') {

            $now = now()->format('Y-m-d_H-i');

            $setting = GeneralSetting::first();

            $html = view('admin.pages.accounts.pdf', [
                'accounts' => $data,
                'date_range' => $request->date_range,
                'filter' => $request->filter,
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
                    'title' => $item->title,
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
                    'title' => 'Invoice Payment',
                    'type' => 'income',
                    'amount' => $item->amount,
                    'category' => 'Invoice',
                    'note' => 'Paid for Share: '
                        . ($item->investor->package->share_name ?? 'N/A')
                        . ' by '
                        . ($item->user->email ?? 'N/A'),
                ];
            });

        // MERGE
        $all = collect()->merge($accounts)->merge($invoices);

        // FILTER
        if ($filter) {

            if (in_array($filter, ['income', 'expense'])) {
                $all = $all->where('type', $filter);
            }

            if (str_starts_with($filter, 'cat_')) {
                $cat = str_replace('cat_', '', $filter);
                $all = $all->where('category', $cat);
            }
        }

        // SEARCH
        if ($search) {
            $all = $all->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['title']), strtolower($search)) ||
                    str_contains(strtolower($item['note']), strtolower($search)) ||
                    str_contains(strtolower($item['category']), strtolower($search)) ||
                    str_contains((string)$item['amount'], $search);
            });
        }

        return $all->sortByDesc('date')->values();
    }
}
