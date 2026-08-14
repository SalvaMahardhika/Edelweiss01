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

        // 🟢 FILTER TIPE PESANAN
        if ($request->filled('order_type') && $request->order_type !== 'ALL') {
            $query->where('order_type', $request->order_type);
        }

        // 🟢 FITUR BARU: FILTER SKEMA PEMBAYARAN (Full Payment vs DP)
        if ($request->filled('payment_scheme') && $request->payment_scheme !== 'ALL') {
            $scheme = strtolower($request->payment_scheme);
            if ($scheme === 'dp') {
                $query->where(function ($q) {
                    $q->where('payment_plan', 'dp')
                        ->orWhere('payment_plan', 'like', '%dp%');
                });
            } elseif ($scheme === 'full') {
                $query->where(function ($q) {
                    $q->where('payment_plan', 'full')
                        ->orWhere('payment_plan', 'like', '%full%')
                        ->orWhereNull('payment_plan');
                });
            }
        }

        // -------------------------------------------------------------
        // ⚡ DATATABLES AJAX SERVER-SIDE PROCESSING (REALTIME UPDATES)
        // -------------------------------------------------------------
        if ($request->ajax()) {
            $dataTablesQuery = clone $query;

            // Global Live Search Handler (Nama Pelanggan & No HP)
            $searchValue = trim($request->input('search.value', $request->input('search', '')));

            if (! empty($searchValue)) {
                $dataTablesQuery->where(function ($q) use ($searchValue) {
                    $q->where('customer_name', 'like', "%{$searchValue}%")
                        ->orWhere('customer_phone', 'like', "%{$searchValue}%")
                        ->orWhere('order_number', 'like', "%{$searchValue}%");
                });
            }

            // 💵 Total Uang Masuk / Cashflow
            $totalCashflowRealtime = (float) (clone $query)->sum('total_amount');
            $totalPesanan = (clone $query)->count();

            // 🏆 Total Omzet Terrealisasi (Hanya Order Status Completed & Tanggal Pengambilan <= Hari Ini)
            $realizedQuery = Order::where('status', 'completed')
                ->where(DB::raw('DATE(COALESCE(fulfill_at, placed_at, created_at))'), '<=', $todayDate)
                ->whereBetween(DB::raw('DATE(COALESCE(placed_at, created_at))'), [$startDate, $endDate]);

            if ($request->filled('order_type') && $request->order_type !== 'ALL') {
                $realizedQuery->where('order_type', $request->order_type);
            }

            if ($request->filled('payment_scheme') && $request->payment_scheme !== 'ALL') {
                $scheme = strtolower($request->payment_scheme);
                if ($scheme === 'dp') {
                    $realizedQuery->where(function ($q) {
                        $q->where('payment_plan', 'dp')
                            ->orWhere('payment_plan', 'like', '%dp%');
                    });
                } elseif ($scheme === 'full') {
                    $realizedQuery->where(function ($q) {
                        $q->where('payment_plan', 'full')
                            ->orWhere('payment_plan', 'like', '%full%')
                            ->orWhereNull('payment_plan');
                    });
                }
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

                if ($request->filled('payment_scheme') && $request->payment_scheme !== 'ALL') {
                    $scheme = strtolower($request->payment_scheme);
                    if ($scheme === 'dp') {
                        $q->where(function ($sub) {
                            $sub->where('payment_plan', 'dp')
                                ->orWhere('payment_plan', 'like', '%dp%');
                        });
                    } elseif ($scheme === 'full') {
                        $q->where(function ($sub) {
                            $sub->where('payment_plan', 'full')
                                ->orWhere('payment_plan', 'like', '%full%')
                                ->orWhereNull('payment_plan');
                        });
                    }
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

                if ($request->filled('payment_scheme') && $request->payment_scheme !== 'ALL') {
                    $scheme = strtolower($request->payment_scheme);
                    if ($scheme === 'dp') {
                        $q->where(function ($sub) {
                            $sub->where('payment_plan', 'dp')
                                ->orWhere('payment_plan', 'like', '%dp%');
                        });
                    } elseif ($scheme === 'full') {
                        $q->where(function ($sub) {
                            $sub->where('payment_plan', 'full')
                                ->orWhere('payment_plan', 'like', '%full%')
                                ->orWhereNull('payment_plan');
                        });
                    }
                }
            })
                ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
                ->groupBy('product_name')
                ->orderByDesc('total_qty')
                ->take(5)
                ->get();

            // 📈 Dynamic Chart Realtime
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
                ->when($request->filled('payment_scheme') && $request->payment_scheme !== 'ALL', function ($q) use ($request) {
                    $scheme = strtolower($request->payment_scheme);
                    if ($scheme === 'dp') {
                        $q->where(function ($sub) {
                            $sub->where('payment_plan', 'dp')
                                ->orWhere('payment_plan', 'like', '%dp%');
                        });
                    } elseif ($scheme === 'full') {
                        $q->where(function ($sub) {
                            $sub->where('payment_plan', 'full')
                                ->orWhere('payment_plan', 'like', '%full%')
                                ->orWhereNull('payment_plan');
                        });
                    }
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
                // 1. Kolom Waktu (Tgl Pesan & Tgl Selesai)
                ->editColumn('fulfill_at', function ($row) {
                    $placedDate = $row->placed_at ?? $row->created_at;
                    $fulfillDate = $row->fulfill_at ?? $placedDate;

                    $placedFormatted = $placedDate ? Carbon::parse($placedDate)->translatedFormat('d M Y') : '-';
                    $fulfillDateFormatted = $fulfillDate ? Carbon::parse($fulfillDate)->translatedFormat('d M Y') : '-';
                    $fulfillTimeFormatted = $fulfillDate ? Carbon::parse($fulfillDate)->format('H:i').' WIB' : '';

                    return '
                        <div class="space-y-0.5">
                            <p class="text-[11px] font-medium text-gray-500">Pesan: <span class="font-bold text-gray-700">'.$placedFormatted.'</span></p>
                            <p class="text-xs font-bold text-[#3e2723]">Selesai: '.$fulfillDateFormatted.'</p>
                            <p class="text-[10px] font-semibold text-amber-800">'.$fulfillTimeFormatted.'</p>
                        </div>
                    ';
                })
                // 2. Kolom Order, Pelanggan, & Ringkasan Produk
                ->editColumn('order_number', function ($row) {
                    $itemsSummary = $row->items->map(fn($i) => $i->quantity.'x '.$i->product_name)->implode(', ');
                    if (empty($itemsSummary)) {
                        $itemsSummary = 'Tidak ada detail produk';
                    }

                    return '
                        <div>
                            <p class="font-black text-[#3e2723] text-xs">'.e($row->order_number).'</p>
                            <p class="text-xs font-bold text-gray-800">'.e($row->customer_name).' <span class="text-[10px] font-normal text-gray-500">('.e($row->customer_phone).')</span></p>
                            <p class="text-[11px] font-medium text-amber-900/80 truncate max-w-xs mt-0.5" title="'.e($itemsSummary).'">
                                <i class="fa-solid fa-basket-shopping text-[10px]"></i> '.e($itemsSummary).'
                            </p>
                        </div>
                    ';
                })
                // 3. Kolom Tipe Layanan & Akses Alamat
                ->editColumn('order_type', function ($row) {
                    $typeVal = is_object($row->order_type) ? ($row->order_type->value ?? $row->order_type->name ?? (string) $row->order_type) : (string) $row->order_type;
                    $isDelivery = strtolower($typeVal) === 'delivery';
                    $cls = $isDelivery ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800';
                    $addressAttr = e($row->delivery_address ?? 'Alamat tidak diisi');
                    $notesAttr = e($row->notes ?? '-');

                    $html = '<div class="text-center space-y-1">';
                    $html .= '<span class="uppercase px-2 py-0.5 rounded-md text-[9px] font-bold '.$cls.'">'.e($typeVal).'</span>';

                    if ($isDelivery) {
                        $html .= '<br><button type="button" onclick="showAddressModal(\''.e($row->order_number).'\', \''.e($row->customer_name).'\', \''.$addressAttr.'\', \''.$notesAttr.'\')" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-700 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-2 py-0.5 rounded-md border border-blue-200 transition">
                            <i class="fa-solid fa-location-dot text-[9px]"></i> Detail Alamat
                        </button>';
                    } else {
                        $html .= '<p class="text-[10px] font-semibold text-gray-500">Pickup Toko</p>';
                    }

                    $html .= '</div>';

                    return $html;
                })
                // 4. Kolom Skema Awal & Metode Pembayaran
                ->addColumn('payment_scheme', function ($row) {
                    $planVal = is_object($row->payment_plan) ? ($row->payment_plan->value ?? $row->payment_plan->name ?? (string) $row->payment_plan) : (string) $row->payment_plan;
                    $isDp = strtolower($planVal) === 'dp' || str_contains(strtolower($planVal), 'dp');
                    
                    $methodVal = $row->payment_method ?? 'offline_store';
                    if ($methodVal === 'offline_store') {
                        $methodLabel = 'Kasir Offline';
                    } elseif ($methodVal === 'manual_wa') {
                        $methodLabel = 'Manual WA';
                    } else {
                        $methodLabel = 'Payment Gateway';
                    }

                    $schemeBadge = $isDp 
                        ? '<span class="px-2 py-0.5 bg-amber-500/10 text-amber-900 border border-amber-400/30 rounded-md text-[9px] font-bold">DP Awal (50%)</span>' 
                        : '<span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-900 border border-emerald-400/30 rounded-md text-[9px] font-bold">Full Payment</span>';

                    return '
                        <div class="space-y-1 text-center sm:text-left">
                            '.$schemeBadge.'
                            <p class="text-[10px] font-semibold text-gray-500">Via: '.$methodLabel.'</p>
                        </div>
                    ';
                })
                // 5. Kolom Total Nominal
                ->editColumn('total_amount', function ($row) {
                    return '
                        <div class="text-right">
                            <p class="font-black text-xs text-emerald-950">Rp '.number_format((float) $row->total_amount, 0, ',', '.').'</p>
                            <span class="text-[9px] font-bold uppercase text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded">Lunas 100%</span>
                        </div>
                    ';
                })
                ->rawColumns(['fulfill_at', 'order_number', 'order_type', 'payment_scheme', 'total_amount'])
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

        if ($request->filled('payment_scheme') && $request->payment_scheme !== 'ALL') {
            $scheme = strtolower($request->payment_scheme);
            if ($scheme === 'dp') {
                $realizedQuery->where(function ($q) {
                    $q->where('payment_plan', 'dp')
                        ->orWhere('payment_plan', 'like', '%dp%');
                });
            } elseif ($scheme === 'full') {
                $realizedQuery->where(function ($q) {
                    $q->where('payment_plan', 'full')
                        ->orWhere('payment_plan', 'like', '%full%')
                        ->orWhereNull('payment_plan');
                });
            }
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

            if ($request->filled('payment_scheme') && $request->payment_scheme !== 'ALL') {
                $scheme = strtolower($request->payment_scheme);
                if ($scheme === 'dp') {
                    $q->where(function ($sub) {
                        $sub->where('payment_plan', 'dp')
                            ->orWhere('payment_plan', 'like', '%dp%');
                    });
                } elseif ($scheme === 'full') {
                    $q->where(function ($sub) {
                        $sub->where('payment_plan', 'full')
                            ->orWhere('payment_plan', 'like', '%full%')
                            ->orWhereNull('payment_plan');
                    });
                }
            }
        })->sum('quantity');

        $topProducts = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate, $request) {
            $q->whereNotIn('status', ['cancelled'])
                ->whereBetween(DB::raw('DATE(COALESCE(placed_at, created_at))'), [$startDate, $endDate]);

            if ($request->filled('order_type') && $request->order_type !== 'ALL') {
                $q->where('order_type', $request->order_type);
            }

            if ($request->filled('payment_scheme') && $request->payment_scheme !== 'ALL') {
                $scheme = strtolower($request->payment_scheme);
                if ($scheme === 'dp') {
                    $q->where(function ($sub) {
                        $sub->where('payment_plan', 'dp')
                            ->orWhere('payment_plan', 'like', '%dp%');
                    });
                } elseif ($scheme === 'full') {
                    $q->where(function ($sub) {
                        $sub->where('payment_plan', 'full')
                            ->orWhere('payment_plan', 'like', '%full%')
                            ->orWhereNull('payment_plan');
                    });
                }
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
            ->when($request->filled('payment_scheme') && $request->payment_scheme !== 'ALL', function ($q) use ($request) {
                $scheme = strtolower($request->payment_scheme);
                if ($scheme === 'dp') {
                    $q->where(function ($sub) {
                        $sub->where('payment_plan', 'dp')
                            ->orWhere('payment_plan', 'like', '%dp%');
                    });
                } elseif ($scheme === 'full') {
                    $q->where(function ($sub) {
                        $sub->where('payment_plan', 'full')
                            ->orWhere('payment_plan', 'like', '%full%')
                            ->orWhereNull('payment_plan');
                    });
                }
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

        if ($request->filled('payment_scheme') && $request->payment_scheme !== 'ALL') {
            $scheme = strtolower($request->payment_scheme);
            if ($scheme === 'dp') {
                $query->where(function ($q) {
                    $q->where('payment_plan', 'dp')
                        ->orWhere('payment_plan', 'like', '%dp%');
                });
            } elseif ($scheme === 'full') {
                $query->where(function ($q) {
                    $q->where('payment_plan', 'full')
                        ->orWhere('payment_plan', 'like', '%full%')
                        ->orWhereNull('payment_plan');
                });
            }
        }

        $orders = $query->latest('created_at')->get();

        $fileName = 'Laporan_Penjualan_Edelweiss_'.date('Y-m-d_H-i').'.csv';

        $tempFile = fopen('php://temp', 'r+');
        fwrite($tempFile, "\xEF\xBB\xBF");

        fputcsv($tempFile, [
            'No. Order',
            'Tanggal Pemesanan',
            'Tanggal Penyerahan/Selesai',
            'Nama Pelanggan',
            'No. HP',
            'Tipe Pesanan',
            'Alamat Pengiriman',
            'Item Kue / Roti',
            'Skema Pembayaran Awal',
            'Status Pelunasan',
            'Total Nominal (Rp)',
        ]);

        foreach ($orders as $order) {
            $itemList = $order->items->map(function ($i) {
                return $i->quantity.'x '.str_replace(["\r", "\n", '"'], '', $i->product_name);
            })->implode(' | ');

            $placedDate = $order->placed_at ?? $order->created_at;
            $fulfillDate = $order->fulfill_at ?? $order->placedDate;

            $orderTypeVal = is_object($order->order_type) ? ($order->order_type->value ?? $order->order_type->name ?? (string) $order->order_type) : (string) $order->order_type;
            $paymentPlanVal = is_object($order->payment_plan) ? ($order->payment_plan->value ?? $order->payment_plan->name ?? (string) $order->payment_plan) : (string) $order->payment_plan;

            fputcsv($tempFile, [
                $order->order_number,
                $placedDate ? date('d/m/Y H:i', strtotime($placedDate)) : '-',
                $fulfillDate ? date('d/m/Y H:i', strtotime($fulfillDate)) : '-',
                $order->customer_name,
                ' '.$order->customer_phone,
                strtoupper($orderTypeVal),
                $order->delivery_address ?? '-',
                $itemList,
                strtoupper($paymentPlanVal),
                'LUNAS 100%',
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