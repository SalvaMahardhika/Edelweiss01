<?php

namespace App\Http\Controllers;

use App\Models\DisabledDate;
use Illuminate\Http\Request;

class DisabledDateController extends Controller
{
    /**
     * Tampilkan halaman kelola tanggal terblokir / lock tanggal.
     */
    public function index(Request $request)
    {
        // Otomatis hapus tanggal terblokir yang sudah lewat (arsip pembersihan otomatis)
        DisabledDate::where('date', '<', now()->toDateString())->delete();

        $query = DisabledDate::orderBy('date', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('date', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $disabledDates = $query->paginate(10)->withQueryString();

        return view('admin.disabled_dates.index', compact('disabledDates'));
    }

    /**
     * Kunci / blokir tanggal baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today|unique:disabled_dates,date',
            'reason' => 'nullable|string|max:255',
        ], [
            'date.required' => 'Tanggal wajib dipilih.',
            'date.after_or_equal' => 'Tanggal yang dikunci tidak boleh tanggal yang sudah lewat.',
            'date.unique' => 'Tanggal tersebut sudah dikunci sebelumnya.',
            'reason.max' => 'Alasan maksimal 255 karakter.',
        ]);

        DisabledDate::create([
            'date' => $request->date,
            'reason' => $request->reason ?? 'Kuota Penuh / Toko Libur',
        ]);

        return back()->with('success', 'Tanggal '.date('d M Y', strtotime($request->date)).' berhasil dikunci.');
    }

    /**
     * Buka kembali tanggal yang dikunci (hapus pembatasan).
     */
    public function destroy($id)
    {
        $dateRecord = DisabledDate::findOrFail($id);
        $formattedDate = date('d M Y', strtotime($dateRecord->date));

        $dateRecord->delete();

        return back()->with('success', 'Kunci tanggal '.$formattedDate.' berhasil dibuka kembali.');
    }
}
