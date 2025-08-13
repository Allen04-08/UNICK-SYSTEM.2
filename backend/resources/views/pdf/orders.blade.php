<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orders Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
<h2>Orders Report</h2>
<table>
    <thead>
    <tr>
        <th>Order #</th>
        <th>Customer</th>
        <th>Status</th>
        <th>Total</th>
        <th>Items</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $order)
        <tr>
            <td>{{ $order->order_number }}</td>
            <td>{{ $order->user->name ?? '' }}</td>
            <td>{{ $order->status }}</td>
            <td>{{ number_format($order->total, 2) }}</td>
            <td>
                @foreach($order->items as $item)
                    {{ $item->product->sku ?? '' }} x {{ $item->quantity }}<br>
                @endforeach
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>