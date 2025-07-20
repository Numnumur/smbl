<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pelanggan</title>
    <style>
        @page {
            margin: 40px;
            margin-top: 80px;
            /* Tambahan space untuk header di setiap halaman */
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            /* Reset margin karena sudah diatur di @page */
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 20px;
            margin-top: 0;
            /* Reset margin top untuk h1 */
        }

        h2 {
            margin-top: 40px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 16px;
        }

        .statistic-table {
            border-collapse: collapse;
            width: auto;
            font-size: 13px;
            border: none;
            margin-bottom: 30px;
        }

        .statistic-table td {
            padding: 4px 6px;
            vertical-align: top;
            white-space: nowrap;
            border: none;
        }

        .statistic-table td:nth-child(1),
        .statistic-table td:nth-child(2) {
            width: 1px;
            white-space: nowrap;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        /* Style untuk tabel utama agar memiliki space yang konsisten */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
        }

        /* Pastikan header tabel berulang di setiap halaman */
        thead {
            display: table-header-group;
        }

        tbody {
            display: table-row-group;
        }

        /* Hindari page break di dalam baris tabel */
        tr {
            page-break-inside: avoid;
        }

        td.center {
            text-align: center;
        }

        td.left {
            text-align: left;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <h1>{{ $name }}</h1>

    <table class="statistic-table">
        <tr>
            <td><strong>Periode</strong></td>
            <td>:</td>
            <td>{{ $startDate }} - {{ $endDate }}</td>
        </tr>
        <tr>
            <td><strong>Total Pelanggan</strong></td>
            <td>:</td>
            <td>{{ $customers->count() }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th class="center">No</th>
                <th class="center">Nama Pelanggan</th>
                <th class="center">Pesanan</th>
                <th class="center">Pemasukan</th>
                <th class="center">Paket Pesanan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $index => $customer)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="left">{{ $customer['name'] }}</td>
                    <td class="left">
                        Total: {{ $customer['total_orders'] }}<br>
                        Terakhir: {{ $customer['last_order_date'] }}<br>
                        <small>{{ $customer['last_order_diff'] }}</small>
                    </td>
                    <td class="left">
                        Total: Rp. {{ number_format($customer['total_income'], 0, ',', '.') }}<br>
                        Rata-rata Per Pesanan: Rp. {{ number_format($customer['average_income'], 0, ',', '.') }}
                    </td>
                    <td class="left">
                        @foreach ($customer['packages'] as $package => $count)
                            - {{ $package }} ({{ $count }})<br>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
