<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan – Kobana Fried Chicken</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; padding: 20px; }
        h1 { font-size: 16px; text-align: center; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #555; margin-bottom: 16px; font-size: 11px; }
        .summary { display: flex; gap: 12px; margin-bottom: 16px; }
        .card { border: 1px solid #ddd; border-radius: 6px; padding: 10px 14px; flex: 1; text-align: center; }
        .card .label { font-size: 10px; color: #666; text-transform: uppercase; }
        .card .value { font-size: 15px; font-weight: bold; }
        h2 { font-size: 13px; margin: 12px 0 6px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead tr { background: #f5f5f5; }
        th, td { border: 1px solid #ddd; padding: 5px 8px; text-align: left; }
        th { font-size: 11px; }
        .right { text-align: right; }
        .center { text-align: center; }
        tfoot tr { background: #eee; font-weight: bold; }
        .cat-row { background: #e8f4f8; font-weight: bold; }
        @media print {
            body { padding: 0; }
            button { display: none; }
        }
    </style>
</head>
<body>

<h1>Laporan Penjualan – Kobana Fried Chicken</h1>
<p class="subtitle">
    Periode: {{ $dateFrom->translatedFormat('d F Y') }} – {{ $dateTo->translatedFormat('d F Y') }} |
    Dicetak: {{ now()->format('d/m/Y H:i') }}
</p>

<button onclick="window.print()" style="margin-bottom:12px; padding:6px 14px; cursor:pointer;">🖨 Print / Simpan PDF</button>

<div class="summary">
    <div class="card">
        <div class="label">Total Omzet</div>
        <div class="value">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
    </div>
    <div class="card">
        <div class="label">Total Transaksi</div>
        <div class="value">{{ number_format($totalTrx, 0, ',', '.') }}</div>
    </div>
    <div class="card">
        <div class="label">Item Terjual</div>
        <div class="value">{{ number_format($totalItem, 0, ',', '.') }}</div>
    </div>
    <div class="card">
        <div class="label">Rata-rata / Trx</div>
        <div class="value">Rp {{ number_format($avgTrx, 0, ',', '.') }}</div>
    </div>
</div>

<h2>Laporan per Menu</h2>
<table>
    <thead>
        <tr>
            <th>Kategori</th>
            <th>Kode</th>
            <th>Nama Menu</th>
            <th class="center">Qty</th>
            <th class="right">Omzet (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @php $lastKat = null; @endphp
        @foreach($menuReport as $m)
            @if($m->kategori !== $lastKat)
                @php $lastKat = $m->kategori; @endphp
                <tr class="cat-row">
                    <td colspan="5">{{ $m->kategori }}</td>
                </tr>
            @endif
            <tr>
                <td></td>
                <td>{{ $m->kode }}</td>
                <td>{{ $m->nama }}</td>
                <td class="center">{{ number_format($m->qty, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($m->omzet, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">Total</td>
            <td class="center">{{ number_format($menuReport->sum('qty'), 0, ',', '.') }}</td>
            <td class="right">{{ number_format($menuReport->sum('omzet'), 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

</body>
</html>
