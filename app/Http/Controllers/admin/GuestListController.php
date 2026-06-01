<?php

namespace App\Http\Controllers\admin;

use App\Exports\GuestListExport;
use App\Http\Controllers\Controller;
use App\Models\GuestList;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class GuestListController extends Controller
{
    public function index(Request $request)
    {
        $query = GuestList::query();

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $guestLists = $query
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.pages.guest-list.index', compact('guestLists'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profession' => 'nullable|string|max:255',
            'status' => 'required|in:Interested,Highly Motivated,Not Interested',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        GuestList::create([
            'date' => $request->date,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'profession' => $request->profession,
            'status' => $request->status,
            'reference' => $request->reference,
            'note' => $request->note,
        ]);

        return redirect()->route('admin.guest-list.index')->with('success', 'Guest added successfully.');
    }

    public function export($type)
    {
        $data = GuestList::latest()->get();

        // CSV / Excel
        if ($type == 'csv' || $type == 'excel') {
            return Excel::download(
                new GuestListExport,
                'guest-list.' . ($type == 'excel' ? 'xlsx' : 'csv')
            );
        }

        // PDF
        if ($type == 'pdf') {

            $pdf = Pdf::loadView('admin.pages.guest-list.pdf', [
                'guestLists' => $data
            ]);

            return $pdf->download('guest-list.pdf');
        }

        abort(404);
    }
}
