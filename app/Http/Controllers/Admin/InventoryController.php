<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with('product')->latest();

        if ($request->product) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->product . '%');
            });
        }

        if ($request->tipe) {
            $query->where('tipe', $request->tipe);
        }

        $purchases = Purchase::latest()->get();

        return Inertia::render('Admin/Inventory/Index', [
            'products' => Product::all(),
            'logs' => $query->get(),
            'purchases' => $purchases,
            'filters' => $request->only('product', 'tipe'),
        ]);
    }

    public function storeMasuk(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string'
        ]);

        Inventory::create([
            'product_id' => $data['product_id'],
            'jumlah' => $data['jumlah'],
            'tipe' => 'masuk',
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        $product = Product::find($data['product_id']);
        $product->increment('stok', $data['jumlah']);

        return redirect()->route('inventory.index');
    }

    public function storeKeluar(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string'
        ]);

        $product = Product::find($data['product_id']);
        if ($product->stok < $data['jumlah']) {
            return back()->withErrors(['jumlah' => 'Stok tidak cukup']);
        }

        Inventory::create([
            'product_id' => $data['product_id'],
            'jumlah' => $data['jumlah'],
            'tipe' => 'keluar',
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        $product->decrement('stok', $data['jumlah']);

        return redirect()->route('inventory.index');
    }

    public function report(Request $request)
    {
        $tipe = $request->tipe;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
    
        $query = Inventory::with('product');
    
        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        } else {
            // Default hari ini
            $query->whereDate('created_at', now());
        }
    
        if ($tipe) {
            $query->where('tipe', $tipe);
        }
    
        $grouped = $query->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });
    
        $report = $grouped->map(function ($items, $label) {
            $masuk = $keluar = $pendapatan = 0;
            foreach ($items as $item) {
                if ($item->tipe === 'masuk') {
                    $masuk += $item->jumlah;
                } else {
                    $keluar += $item->jumlah;
                    $pendapatan += $item->jumlah * ($item->product->harga ?? 0);
                }
            }
            return [
                'tanggal' => $label,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'pendapatan' => $pendapatan,
            ];
        })->values();
    
        return Inertia::render('Admin/Inventory/Laporan', [
            'dailyReport' => $report,
            'filters' => $request->only('start_date', 'end_date', 'tipe'),
        ]);
    }
    

    public function export(Request $request)
    {
        $data = Inventory::with('product')->get();

        $pdf = Pdf::loadView('pdf.inventory', ['data' => $data]);
        return $pdf->download('inventory.pdf');
    }
}
