<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1, h2, p { text-align: center; margin: 0 0 6px; }
        h1 { font-size: 18px; } h2 { font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #444; padding: 5px; vertical-align: top; }
        th { background: #e9ecef; text-align: center; }
        .text-right { text-align: right; } .summary { margin-top: 12px; text-align: right; }
    </style>
</head>
<body>
    <h1>WARRANTY SYSTEM</h1>
    <h2>{{ strtoupper($title) }}</h2>
    <p>Periode: {{ $tanggal_mulai ?: 'Awal' }} s/d {{ $tanggal_akhir ?: 'Sekarang' }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                @if($type === 'INVOICE')
                    <th>No Invoice</th><th>Tanggal</th><th>No Order</th><th>Customer</th><th>Type</th><th>Grand Total</th><th>Paid</th><th>Outstanding</th><th>Status</th>
                @else
                    <th>No Order</th><th>Tanggal</th><th>Customer</th><th>Building</th><th>Produk</th><th>Teknisi</th><th>Status</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @if($type === 'INVOICE')
                        <td>{{ $row->invoice_number }}</td><td>{{ $row->invoice_date }}</td><td>{{ $row->order_number }}</td><td>{{ $row->nama_customer }}</td><td>{{ $row->order_type }}</td>
                        <td class="text-right">Rp {{ number_format($row->grand_total, 2, ',', '.') }}</td><td class="text-right">Rp {{ number_format($row->paid_amount, 2, ',', '.') }}</td><td class="text-right">Rp {{ number_format($row->outstanding_amount, 2, ',', '.') }}</td><td>{{ $row->status }}</td>
                    @else
                        <td>{{ $row->order_number }}</td><td>{{ $row->order_date }}</td><td>{{ $row->nama_customer }}</td><td>{{ $row->nama_bangunan }}</td><td>{{ $row->products }}</td><td>{{ $row->installer }}</td><td>{{ $row->status }}</td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ $type === 'INVOICE' ? 10 : 8 }}" style="text-align:center">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($type === 'INVOICE' && $summary)
        <div class="summary">
            <p>Total Grand Total: <strong>Rp {{ number_format($summary['grand_total'], 2, ',', '.') }}</strong></p>
            <p>Total Paid: <strong>Rp {{ number_format($summary['paid_amount'], 2, ',', '.') }}</strong></p>
            <p>Total Outstanding: <strong>Rp {{ number_format($summary['outstanding_amount'], 2, ',', '.') }}</strong></p>
        </div>
    @endif
</body>
</html>
