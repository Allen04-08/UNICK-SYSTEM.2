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
        $summary = [
            'batches_by_status' => \App\Models\ProductionBatch::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')->pluck('count','status'),
            'recent_logs' => \App\Models\ProductionLog::latest()->limit(20)->get(),
        ];
        return response()->json($summary);
    }

    public function productionCsv()
    {
        $rows = \App\Models\ProductionBatch::select('batch_number','status','quantity_planned','quantity_completed','start_date','due_date')->orderByDesc('id')->limit(200)->get();
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['Batch #','Status','Qty Planned','Qty Completed','Start','Due']);
        foreach ($rows as $r) {
            fputcsv($csv, [$r->batch_number,$r->status,$r->quantity_planned,$r->quantity_completed,$r->start_date,$r->due_date]);
        }
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);
        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="production.csv"'
        ]);
    }

    public function inventoryCsv()
    {
        $rows = Product::orderBy('name')->get(['sku','name','stock_on_hand','stock_allocated','reorder_point','safety_stock','lead_time_days']);
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['SKU','Name','On Hand','Allocated','Reorder Point','Safety Stock','Lead Time (days)']);
        foreach ($rows as $r) {
            fputcsv($csv, [$r->sku,$r->name,$r->stock_on_hand,$r->stock_allocated,$r->reorder_point,$r->safety_stock,$r->lead_time_days]);
        }
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);
        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory.csv"'
        ]);
    }

    public function inventoryExcel()
    {
        $this->authorize('viewAny', InventoryMovement::class);
        $rows = Product::orderBy('name')->get(['sku','name','stock_on_hand','stock_allocated','reorder_point','safety_stock','lead_time_days']);
        return Excel::download(new class($rows) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $rows;
            public function __construct($rows) { $this->rows = $rows->map(fn($r) => [$r->sku,$r->name,$r->stock_on_hand,$r->stock_allocated,$r->reorder_point,$r->safety_stock,$r->lead_time_days])->values()->all(); }
            public function array(): array { return $this->rows; }
            public function headings(): array { return ['SKU','Name','On Hand','Allocated','Reorder Point','Safety Stock','Lead Time (days)']; }
        }, 'inventory.xlsx');
    }

    public function productionExcel()
    {
        $this->authorize('viewAny', InventoryMovement::class);
        $rows = \App\Models\ProductionBatch::select('batch_number','status','quantity_planned','quantity_completed','start_date','due_date')->orderByDesc('id')->limit(200)->get();
        return Excel::download(new class($rows) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $rows;
            public function __construct($rows) { $this->rows = $rows->map(fn($r) => [$r->batch_number,$r->status,$r->quantity_planned,$r->quantity_completed,$r->start_date,$r->due_date])->values()->all(); }
            public function array(): array { return $this->rows; }
            public function headings(): array { return ['Batch #','Status','Qty Planned','Qty Completed','Start','Due']; }
        }, 'production.xlsx');
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

    public function ordersCsv()
    {
        $this->authorize('viewAny', Order::class);
        $orders = Order::with('items.product','user')->latest()->limit(500)->get();
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['Order #','Customer','Status','SKU','Product','Qty','Unit Price','Line Total','Order Total']);
        foreach ($orders as $o) {
            foreach ($o->items as $i) {
                fputcsv($csv, [
                    $o->order_number,
                    $o->user->name ?? '',
                    $o->status,
                    $i->product->sku ?? '',
                    $i->product->name ?? '',
                    $i->quantity,
                    $i->unit_price,
                    $i->line_total,
                    $o->total,
                ]);
            }
        }
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);
        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders.csv"'
        ]);
    }
}
