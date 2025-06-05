<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Inventory</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #eee;
        }
        h2 {
            text-align: center;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <h2>Laporan Inventory</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Produk</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
                <th>Tanggal</th>
                <th>Harga Satuan</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->product->nama ?? '-' }}</td>
                    <td>{{ ucfirst($item->tipe) }}</td>
                    <td>{{ $item->jumlah }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                    <td>{{ $item->created_at->format('Y-m-d') }}</td>
                    <td>@currency($item->product->harga ?? 0)</td>
                    <td>@currency(($item->product->harga ?? 0) * $item->jumlah)</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Total Data:</strong> {{ $data->count() }}</p>
</body>
</html>
