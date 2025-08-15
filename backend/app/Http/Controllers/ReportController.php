<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function orders()
    {
        $this->authorize('viewAny', Order::class);
        $summary = [
            'by_status' => Order::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')->pluck('count','status'),
            'recent' => Order::latest()->limit(10)->get(),
        ];
        return response()->json($summary);
    }

    public function inventory()
    {
        $this->authorize('viewAny', InventoryMovement::class);
        $lowStock = Product::whereRaw('stock_on_hand <= reorder_point + safety_stock')
            ->orderBy('stock_on_hand')
            ->limit(20)
            ->get();
        return response()->json(['low_stock' => $lowStock]);
    }

    public function production()
    {
        return response()->json(['message' => 'Production report placeholder']);
    }

    public function ordersExcel()
    {
        $this->authorize('viewAny', Order::class);
        $orders = Order::with('items.product','user')->get();

        return Excel::download(new class($orders) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            private array $rows;
            public function __construct($orders) { $this->rows = $orders->flatMap(function ($o) {
                return $o->items->map(function ($i) use ($o) {
                    return [
                        $o->order_number,
                        $o->user->name ?? '',
                        $o->status,
                        $i->product->sku ?? '',
                        $i->product->name ?? '',
                        $i->quantity,
                        $i->unit_price,
                        $i->line_total,
                        $o->total,
                    ];
                });
            })->values()->all(); }
            public function array(): array { return $this->rows; }
            public function headings(): array { return ['Order #','Customer','Status','SKU','Product','Qty','Unit Price','Line Total','Order Total']; }
        }, 'orders.xlsx');
    }

    public function ordersPdf()
    {
        $this->authorize('viewAny', Order::class);
        $orders = Order::with('items.product','user')->latest()->limit(50)->get();
        $html = view('pdf.orders', compact('orders'))->render();
        return Pdf::loadHTML($html)->download('orders.pdf');
    }
}
