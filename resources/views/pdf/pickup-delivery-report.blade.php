<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Permintaan Antar Jemput</title>
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

        h2 {
            text-align: center;
            margin-bottom: 10px;
            margin-top: 40px;
        }

        table.statistic-table {
            border-collapse: collapse;
            width: auto;
            font-size: 13px;
            margin-bottom: 60px;
            /* Tambahkan space setelah statistik */
        }

        .statistic-table td {
            padding: 4px 6px;
            vertical-align: top;
            white-space: nowrap;
            border: none;
            /* Hapus border */
        }

        .main-table,
        .main-table th,
        .main-table td {
            border: 1px solid #000;
            border-collapse: collapse;
        }

        table {
            width: 100%;
            margin-top: 30px;
        }

        th,
        td {
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
            <td><strong>Total Permintaan</strong></td>
            <td>:</td>
            <td>{{ $totalRequests }}</td>
        </tr>
        <tr>
            <td><strong>Total Pelanggan</strong></td>
            <td>:</td>
            <td>{{ $totalCustomers }}</td>
        </tr>
    </table>

    <h2>Jumlah Permintaan Berdasarkan Tipe</h2>
    <table class="main-table">
        <thead>
            <tr>
                <th class="center">No</th>
                <th class="center">Tipe</th>
                <th class="center">Jumlah Permintaan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requestsByType as $type => $count)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="left">{{ $type }}</td>
                    <td class="center">{{ $count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Jumlah Permintaan Tiap Pelanggan</h2>
    <table class="main-table">
        <thead>
            <tr>
                <th class="center">No</th>
                <th class="center">Pelanggan</th>
                <th class="center">Total Permintaan</th>
                <th class="center">Detail</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requestsByCustomer as $name => $info)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="left">{{ $name }}</td>
                    <td class="center">{{ $info['total'] }}</td>
                    <td class="left">
                        @foreach ($info['detail'] as $type => $count)
                            {{ $type }} ({{ $count }})<br>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
