<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $name }}</title>
    <style>
        @page {
            margin: 40px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            color: #000;
            /* Memastikan semua teks berwarna hitam */
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 20px;
            margin-top: 0;
        }

        h2 {
            margin-top: 40px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 16px;
        }

        .subtitle {
            text-align: center;
            font-size: 14px;
            color: #555;
            margin-bottom: 25px;
        }

        .statistic-table {
            border-collapse: collapse;
            width: auto;
            font-size: 13px;
            border: none;
            margin-bottom: 30px;
            /* Menghapus margin auto untuk membuat rata kiri */
        }

        .statistic-table td {
            padding: 2px 6px;
            vertical-align: top;
            white-space: nowrap;
            border: none;
        }

        .statistic-table td:nth-child(1) {
            width: 1px;
            white-space: nowrap;
        }

        .statistic-table td:nth-child(2) {
            width: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

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

        thead {
            display: table-header-group;
        }

        tbody {
            display: table-row-group;
        }

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
    <br>
    <br>
    <table class="statistic-table">
        <tr>
            <td><strong>Periode</strong></td>
            <td>:</td>
            <td>{{ $startDate }} - {{ $endDate }} ({{ $totalDays }} hari)</td>
        </tr>
        <tr>
            <td><strong>Total Pelanggan</strong></td>
            <td>:</td>
            <td>{{ number_format($totalCustomers) }}</td>
        </tr>
        <tr>
            <td><strong>Total Pesanan</strong></td>
            <td>:</td>
            <td>{{ number_format($totalOrders) }}</td>
        </tr>
        <tr>
            <td><strong>Total Pemasukan</strong></td>
            <td>:</td>
            <td>Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Rata-rata Pesanan/Pelanggan</strong></td>
            <td>:</td>
            <td>{{ number_format($averageOrdersPerCustomer, 1) }}</td>
        </tr>
        <tr>
            <td><strong>Rata-rata Pemasukan/Pelanggan</strong></td>
            <td>:</td>
            <td>Rp {{ number_format($averageIncomePerCustomer, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Pelanggan Terbanyak Pesanan</strong></td>
            <td>:</td>
            <td>{{ $topCustomerByOrders['name'] ?? '-' }}
                ({{ number_format($topCustomerByOrders['total_orders'] ?? 0) }} pesanan)</td>
        </tr>
        <tr>
            <td><strong>Pelanggan Terbesar Pemasukan</strong></td>
            <td>:</td>
            <td>{{ $topCustomerByIncome['name'] ?? '-' }} (Rp
                {{ number_format($topCustomerByIncome['total_income'] ?? 0, 0, ',', '.') }})</td>
        </tr>
    </table>

    <h2>Detail Pesanan Pelanggan</h2>
    <table class="main-table">
        <thead>
            <tr>
                <th class="center" style="width: 5%">No</th>
                <th class="center" style="width: 20%">Nama Pelanggan</th>
                <th class="center" style="width: 25%">Informasi Pesanan</th>
                <th class="center" style="width: 25%">Pemasukan</th>
                <th class="center" style="width: 25%">Paket Pesanan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $index => $customer)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="left">{{ $customer['name'] }}</td>
                    <td class="left">
                        Total: {{ $customer['total_orders'] }}<br>
                        Terakhir: {{ $customer['last_order_date'] }}<br>
                        <small>{{ $customer['last_order_diff'] }}</small>
                    </td>
                    <td class="left">
                        Total: Rp {{ number_format($customer['total_income'], 0, ',', '.') }}<br>
                        Rata-rata Per Pesanan: Rp {{ number_format($customer['average_income'], 0, ',', '.') }}
                    </td>
                    <td class="left">
                        @foreach ($customer['packages'] as $package => $count)
                            - {{ $package ?: 'Tidak ada paket' }} ({{ $count }})<br>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center">Tidak ada data pelanggan untuk rentang tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
