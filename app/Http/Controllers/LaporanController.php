<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // ⚡ Ensure Date Filter Always Has Fallbacks Even on Blank Request Input
        $startDate = $request->filled('start_date')
            ? $request->input('start_date')
            : now()->startOfMonth()->toDateString();

        $endDate = $request->filled('end_date')
            ? $request->input('end_date')
            : now()->toDateString();

        $todayDate = now()->toDateString();

        // 1. Base Query: Tampilkan SEMUA order yang tidak dibatalkan (non-cancelled) dalam rentang tanggal
        $query = Order::with(['items', 'payments'])
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween(DB::raw('DATE(COALESCE(placed_at, created_at))'), [$startDate, $endDate]);
            });

        if ($request->filled('order_type') && $request->order_type !== 'ALL') {
            $query->where('order_type', $request->order_type);
        }

        // -------------------------------------------------------------
        // ⚡ DATATABLES AJAX SERVER-SIDE PROCESSING (REALTIME UPDATES)
        // -------------------------------------------------------------
        if ($request->ajax()) {
            $dataTablesQuery = clone $query;

            // Global Live Search Handler (HANYA Nama Pelanggan & No HP)
            $searchValue = trim($request->input('search.value', $request->input('search', '')));

            if (! empty($searchValue)) {
                $dataTablesQuery->where(function ($q) use ($searchValue) {
                    $q->where('customer_name', 'like', "%{$searchValue}%")
                        ->orWhere('customer_phone', 'like', "%{$searchValue}%");
                });
            }

            // 💵 Total Uang Masuk / Cashflow (Full Nominal termasuk DP yang dilunasi offline)
            $totalCashflowRealtime = (float) (clone $query)->sum('total_amount');
            $totalPesanan = (clone $query)->count();

            // 🏆 Total Omzet Terrealisasi (Hanya Order Status Completed & Tanggal Pengambilan <= Hari Ini)
            $realizedQuery = Order::where('status', 'completed')
                ->where(DB::raw('DATE(COALESCE(fulfill_at, placed_at, created_at))'), '<=', $todayDate)
                ->whereBetween(DB::raw('DATE(COALESCE(placed_at, created_at))'), [$startDate, $endDate]);

            if ($request->filled('order_type') && $request->order_type !== 'ALL') {
                $realizedQuery->where('order_type', $request->order_type);
            }
            $totalOmzet = (float) $realizedQuery->sum('total_amount');

            // ⚡ Perbaikan: Bulatkan Rata-Rata Order ke Integer
            $avgOrderVal = $totalPesanan > 0 ? round($totalCashflowRealtime / $totalPesanan) : 0;

            // Item Terjual
            $totalProdukTerjual = (int) OrderItem::whereHas('order', function ($q) use ($startDate, $endDate, $request) {
                $q->whereNotIn('status', ['cancelled'])
                    ->whereBetween(DB::raw('DATE(COALESCE(placed_at, created_at))'), [$startDate, $endDate]);

                if ($request->filled('order_type') && $request->order_type !== 'ALL') {
                    $q->where('order_type', $request->order_type);
                }
            })->sum('quantity');

            // 🍩 Dynamic Donut Chart Stats
            $dpCount = (clone $query)->where(function ($q) {
                $q->where('payment_plan', 'dp')
                    ->orWhere('payment_plan', 'like', '%dp%');
            })->count();
            $fullCount = max(0, $totalPesanan - $dpCount);

            $stats = [
                'totalOmzet' => $totalOmzet,
                'totalPesanan' => $totalPesanan,
                'totalProdukTerjual' => $totalProdukTerjual,
                'avgOrderVal' => $avgOrderVal,
                'totalCashflowRealtime' => $totalCashflowRealtime,
                'pendingRealization' => max(0, $totalCashflowRealtime - $totalOmzet),
                'dpCount' => $dpCount,
                'fullCount' => $fullCount,
            ];

            // 👑 Dynamic Top 5 Products Realtime
            $topProducts = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate, $request) {
                $q->whereNotIn('status', ['cancelled'])
                    ->whereBetween(DB::raw('DATE(COALESCE(placed_at, created_at))'), [$startDate, $endDate]);

                if ($request->filled('order_type') && $request->order_type !== 'ALL') {
                    $q->where('order_type', $request->order_type);
                }
            })
                ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
                ->groupBy('product_name')
                ->orderByDesc('total_qty')
                ->take(5)
                ->get();

            // 📈 Dynamic Chart Realtime
            // 1. Cashflow / Uang Masuk
            $rawCashflow = (clone $query)
                ->select(DB::raw('DATE(COALESCE(placed_at, created_at)) as date'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray();

            // 2. Realisasi Omzet (Pesanan Completed)
            $rawRealized = Order::where('status', 'completed')
                ->where(DB::raw('DATE(COALESCE(fulfill_at, placed_at, created_at))'), '<=', $todayDate)
                ->whereBetween(DB::raw('DATE(COALESCE(placed_at, created_at))'), [$startDate, $endDate])
                ->when($request->filled('order_type') && $request->order_type !== 'ALL', function ($q) use ($request) {
                    $q->where('order_type', $request->order_type);
                })
                ->select(
                    DB::raw('DATE(COALESCE(fulfill_at, placed_at, created_at)) as date'),
                    DB::raw('SUM(total_amount) as total')
                )
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray();

            $chartLabels = [];
            $chartCashflow = [];
            $chartRealized = [];

            try {
                $period = CarbonPeriod::create($startDate, $endDate);
                foreach ($period as $date) {
                    $dateKey = $date->format('Y-m-d');
                    $chartLabels[] = $date->format('d M');
                    $chartCashflow[] = (float) ($rawCashflow[$dateKey] ?? 0);
                    $chartRealized[] = (float) ($rawRealized[$dateKey] ?? 0);
                }
            } catch (\Exception $e) {
                $chartLabels = [];
                $chartCashflow = [];
                $chartRealized = [];
            }

            return DataTables::of($dataTablesQuery)
                ->addIndexColumn()
                ->editColumn('fulfill_at', function ($row) {
                    $date = $row->fulfill_at ?? $row->placed_at ?? $row->created_at;
                    if (! $date) {
                        return '<span class="text-xs text-gray-400 italic">-</span>';
                    }

                    $parsed = Carbon::parse($date);

                    return '<p class="font-bold text-xs text-[#3e2723]">'.$parsed->translatedFormat('d M Y').'</p>'
                         .'<p class="text-[11px] font-semibold text-gray-500">'.$parsed->format('H:i').' WIB</p>';
                })
                ->editColumn('order_number', function ($row) {
                    return '<p class="font-bold text-[#3e2723]">'.e($row->order_number).'</p><p class="text-[11px] text-gray-600">'.e($row->customer_name).'</p>';
                })
                ->editColumn('order_type', function ($row) {
                    $typeVal = is_object($row->order_type) ? ($row->order_type->value ?? $row->order_type->name ?? (string) $row->order_type) : (string) $row->order_type;
                    $cls = strtolower($typeVal) === 'pickup' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800';

                    return '<span class="uppercase px-2 py-0.5 rounded-md text-[9px] font-bold '.$cls.'">'.e($typeVal).'</span>';
                })
                ->editColumn('total_amount', function ($row) {
                    return 'Rp '.number_format((float) $row->total_amount, 0, ',', '.');
                })
                ->rawColumns(['fulfill_at', 'order_number', 'order_type', 'total_amount'])
                ->with([
                    'stats' => $stats,
                    'topProducts' => $topProducts,
                    'chart' => [
                        'labels' => $chartLabels,
                        'cashflow' => $chartCashflow,
                        'realized' => $chartRealized,
                    ],
                ])
                ->order(function ($q) use ($request) {
                    if ($request->has('order') && is_array($request->order) && count($request->order) > 0) {
                        $columnIndex = $request->order[0]['column'] ?? null;
                        $columnDir = $request->order[0]['dir'] ?? 'desc';
                        $columns = $request->columns ?? [];

                        if ($columnIndex !== null && isset($columns[$columnIndex])) {
                            $columnName = $columns[$columnIndex]['name'] ?? $columns[$columnIndex]['data'] ?? null;
                            if (in_array($columnName, ['order_number', 'fulfill_at', 'total_amount', 'order_type', 'created_at'])) {
                                $q->orderBy($columnName, $columnDir);

                                return;
                            }
                        }
                    }
                    $q->latest('created_at');
                })
                ->make(true);
        }

        // Non-AJAX Initial Page Render
        $completedOrders = (clone $query)->latest('created_at')->paginate(10)->withQueryString();

        $totalCashflowRealtime = (float) (clone $query)->sum('total_amount');
        $totalPesanan = (clone $query)->count();

        $realizedQuery = Order::where('status', 'completed')
            ->where(DB::raw('DATE(COALESCE(fulfill_at, placed_at, created_at))'), '<=', $todayDate)
            ->whereBetween(DB::raw('DATE(COALESCE(placed_at, created_at))'), [$startDate, $endDate]);

        if ($request->filled('order_type') && $request->order_type !== 'ALL') {
            $realizedQuery->where('order_type', $request->order_type);
        }
        $totalOmzet = (float) $realizedQuery->sum('total_amount');

        // ⚡ Perbaikan: Bulatkan Rata-Rata Order ke Integer
        $avgOrderVal = $totalPesanan > 0 ? round($totalCashflowRealtime / $totalPesanan) : 0;

        $totalProdukTerjual = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate, $request) {
            $q->whereNotIn('status', ['cancelled'])
                ->whereBetween(DB::raw('DATE(COALESCE(placed_at, created_at))'), [$startDate, $endDate]);

            if ($request->filled('order_type') && $request->order_type !== 'ALL') {
                $q->where('order_type', $request->order_type);
            }
        })->sum('quantity');

        $topProducts = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate, $request) {
            $q->whereNotIn('status', ['cancelled'])
                ->whereBetween(DB::raw('DATE(COALESCE(placed_at, created_at))'), [$startDate, $endDate]);

            if ($request->filled('order_type') && $request->order_type !== 'ALL') {
                $q->where('order_type', $request->order_type);
            }
        })
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // 📈 Sales Dual-Dataset Chart
        $rawCashflow = (clone $query)
            ->select(DB::raw('DATE(COALESCE(placed_at, created_at)) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $rawRealized = Order::where('status', 'completed')
            ->where(DB::raw('DATE(COALESCE(fulfill_at, placed_at, created_at))'), '<=', $todayDate)
            ->whereBetween(DB::raw('DATE(COALESCE(placed_at, created_at))'), [$startDate, $endDate])
            ->when($request->filled('order_type') && $request->order_type !== 'ALL', function ($q) use ($request) {
                $q->where('order_type', $request->order_type);
            })
            ->select(
                DB::raw('DATE(COALESCE(fulfill_at, placed_at, created_at)) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $chartLabels = [];
        $chartCashflow = [];
        $chartRealized = [];

        try {
            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $dateKey = $date->format('Y-m-d');
                $chartLabels[] = $date->format('d M');
                $chartCashflow[] = (float) ($rawCashflow[$dateKey] ?? 0);
                $chartRealized[] = (float) ($rawRealized[$dateKey] ?? 0);
            }
        } catch (\Exception $e) {
            $chartLabels = [];
            $chartCashflow = [];
            $chartRealized = [];
        }

        $chartData = $chartRealized;

        // 🍩 Payment Scheme Aggregation
        $dpCount = (clone $query)->where(function ($q) {
            $q->where('payment_plan', 'dp')
                ->orWhere('payment_plan', 'like', '%dp%');
        })->count();

        $fullCount = max(0, $totalPesanan - $dpCount);

        return view('admin.laporan.index', compact(
            'completedOrders',
            'totalOmzet',
            'totalPesanan',
            'totalProdukTerjual',
            'avgOrderVal',
            'totalCashflowRealtime',
            'topProducts',
            'startDate',
            'endDate',
            'chartLabels',
            'chartCashflow',
            'chartRealized',
            'chartData',
            'dpCount',
            'fullCount'
        ));
    }

    public function exportExcel(Request $request)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $startDate = $request->filled('start_date') ? $request->input('start_date') : now()->startOfMonth()->toDateString();
        $endDate = $request->filled('end_date') ? $request->input('end_date') : now()->toDateString();

        $query = Order::with('items')->whereNotIn('status', ['cancelled'])
            ->whereBetween(DB::raw('DATE(COALESCE(placed_at, created_at))'), [$startDate, $endDate]);

        if ($request->filled('order_type') && $request->order_type !== 'ALL') {
            $query->where('order_type', $request->order_type);
        }

        $orders = $query->latest('created_at')->get();

        $fileName = 'Laporan_Penjualan_Edelweiss_'.date('Y-m-d_H-i').'.csv';

        $tempFile = fopen('php://temp', 'r+');
        fwrite($tempFile, "\xEF\xBB\xBF");

        fputcsv($tempFile, [
            'No. Order',
            'Tanggal Pemesanan',
            'Nama Pelanggan',
            'No. HP',
            'Tipe Pesanan',
            'Item Kue / Roti',
            'Status Pembayaran',
            'Total Nominal (Rp)',
        ]);

        foreach ($orders as $order) {
            $itemList = $order->items->map(function ($i) {
                return $i->quantity.'x '.str_replace(["\r", "\n", '"'], '', $i->product_name);
            })->implode(' | ');

            $placedDate = $order->placed_at ?? $order->created_at;

            $orderTypeVal = is_object($order->order_type) ? ($order->order_type->value ?? $order->order_type->name ?? (string) $order->order_type) : (string) $order->order_type;
            $paymentStatusVal = is_object($order->payment_status) ? ($order->payment_status->value ?? $order->payment_status->name ?? (string) $order->payment_status) : (string) $order->payment_status;

            fputcsv($tempFile, [
                $order->order_number,
                $placedDate ? date('d/m/Y H:i', strtotime($placedDate)) : '-',
                $order->customer_name,
                ' '.$order->customer_phone,
                strtoupper($orderTypeVal),
                $itemList,
                strtoupper($paymentStatusVal),
                $order->total_amount,
            ]);
        }

        rewind($tempFile);
        $csvContent = stream_get_contents($tempFile);
        fclose($tempFile);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
