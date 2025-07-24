<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Keluar Masuk Pesanan</title>
    <style>
        @page {
            margin: 40px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 20px;
            margin-top: 0;
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
            <td>{{ $totalCustomers }}</td>
        </tr>
        <tr>
            <td><strong>Total Pesanan Masuk</strong></td>
            <td>:</td>
            <td>{{ $totalEntry }}</td>
        </tr>
        <tr>
            <td><strong>Total Pesanan Keluar</strong></td>
            <td>:</td>
            <td>{{ $totalExit }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" class="center">No</th>
                <th rowspan="2" class="center">Nama Pelanggan</th>
                <th colspan="2" class="center">Jumlah</th>
                <th colspan="2" class="center">Detail Tanggal</th>
            </tr>
            <tr>
                <th class="center">Masuk</th>
                <th class="center">Keluar</th>
                <th class="center">Masuk</th>
                <th class="center">Keluar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="left">{{ $customer['name'] }}</td>
                    <td class="center">{{ $customer['entry'] }}</td>
                    <td class="center">{{ $customer['exit'] }}</td>
                    <td class="left">
                        @forelse ($customer['entry_dates'] as $date)
                            - {{ \Carbon\Carbon::parse($date)->translatedFormat('l, j F Y') }}<br>
                        @empty
                            -
                        @endforelse
                    </td>
                    <td class="left">
                        @forelse ($customer['exit_dates'] as $date)
                            - {{ \Carbon\Carbon::parse($date)->translatedFormat('l, j F Y') }}<br>
                        @empty
                            -
                        @endforelse
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
</body>

</html>
