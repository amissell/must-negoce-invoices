<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_invoices' => Invoice::count(),
            'total_amount' => Invoice::sum('total'),
            'total_products' => Product::count(),
            'recent_invoices' => Invoice::with('client')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'monthly_totals' => Invoice::select(
                    DB::raw("DATE_FORMAT(invoice_date, '%Y-%m') as month"),
                    DB::raw('SUM(total) as total'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
        ];
        
        return view('dashboard.index', compact('stats'));
    }
}