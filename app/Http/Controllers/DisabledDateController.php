<?php

namespace App\Http\Controllers;

use App\Models\DisabledDate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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

        // 1. Ambil SEMUA data tanggal terkunci untuk pemetaan visual Grid Kalender
        $allDisabledDates = DisabledDate::orderBy('date', 'asc')->get();

        // 2. Query data dengan Pagination untuk Tabel Utama
        $query = DisabledDate::orderBy('date', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('date', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $disabledDates = $query->paginate(10)->withQueryString();

        return view('admin.disabled_dates.index', compact('disabledDates', 'allDisabledDates'));
    }

    /**
     * Kunci / blokir tanggal baru (Mendukung Tanggal Tunggal & Rentang Tanggal).
     */
    public function store(Request $request)
    {
        $reason = $request->reason ?? 'Kuota Penuh / Toko Libur';

        // ðŸŸ¢ OPSI A: INPUT RENTANG TANGGAL (RANGE MODE)
        if ($request->input('input_mode') === 'range' && $request->filled('start_date') && $request->filled('end_date')) {
            $request->validate([
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string|max:255',
            ], [
                'start_date.required' => 'Tanggal mulai wajib diisi.',
                'start_date.after_or_equal' => 'Tanggal mulai tidak boleh tanggal yang sudah lewat.',
                'end_date.required' => 'Tanggal selesai wajib diisi.',
                'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
                'reason.max' => 'Alasan maksimal 255 karakter.',
            ]);

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $period = CarbonPeriod::create($startDate, $endDate);

            $addedCount = 0;
            foreach ($period as $date) {
                // Menggunakan firstOrCreate agar tanggal yang sudah dikunci sebelumnya tidak membuat crash/duplicate
                $item = DisabledDate::firstOrCreate(
                    ['date' => $date->format('Y-m-d')],
                    ['reason' => $reason]
                );

                if ($item->wasRecentlyCreated) {
                    $addedCount++;
                }
            }

            if ($addedCount === 0) {
                return back()->with('error', 'Semua tanggal pada rentang tersebut sudah dikunci sebelumnya.');
            }

            return back()->with('success', "Sebanyak {$addedCount} tanggal dalam rentang berhasil dikunci.");
        }

        // ðŸ”µ OPSI B: INPUT TANGGAL TUNGGAL (SINGLE MODE - ALUR AWAL)
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
            'reason' => $reason,
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
