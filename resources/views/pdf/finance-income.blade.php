<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - Pemasukan</title>
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

        td.right {
            text-align: right;
        }

        .section {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        /* Prevent section header and table from breaking across pages */
        .section-container {
            page-break-inside: avoid;
            margin-top: 50px;
        }

        /* If section is too long, allow break but keep header with at least some table content */
        .section-with-table {
            page-break-inside: avoid;
        }

        /* For very long tables, ensure at least header stays with first few rows */
        .table-container {
            page-break-inside: avoid;
        }

        /* Force page break before if needed */
        .page-break {
            page-break-before: always;
        }

        /* Ensure header stays with table */
        .keep-together {
            page-break-inside: avoid;
            margin-top: 50px;
        }

        /* For cases where content is too long, allow break but try to keep logical groups */
        .table-section {
            break-inside: avoid-page;
            page-break-inside: avoid;
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
            <td><strong>Total Pemasukan</strong></td>
            <td>:</td>
            <td>Rp {{ number_format($total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Rata-rata Per Hari</strong></td>
            <td>:</td>
            <td>Rp {{ number_format($averagePerDay, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Rata-rata Per Pesanan</strong></td>
            <td>:</td>
            <td>Rp {{ number_format($averagePerOrder, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Hari Tertinggi</strong></td>
            <td>:</td>
            <td>
                @if ($topDay !== '-')
                    {{ $topDay }} (Rp {{ number_format($topDayAmount, 0, ',', '.') }})
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Hari Terendah</strong></td>
            <td>:</td>
            <td>
                @if ($bottomDay !== '-')
                    {{ $bottomDay }} (Rp {{ number_format($bottomDayAmount, 0, ',', '.') }})
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <div class="keep-together">
        <h2>Pemasukan Berdasarkan Paket Pesanan</h2>
        <table class="main-table">
            <thead>
                <tr>
                    <th class="center">No</th>
                    <th class="center">Paket Pesanan</th>
                    <th class="center">Jumlah Pesanan</th>
                    <th class="center">Total Pemasukan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ordersByPackage as $data)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td class="left">{{ $data['order_package'] }}</td>
                        <td class="center">{{ number_format($data['jumlah_pesanan']) }}</td>
                        <td class="right">Rp {{ number_format($data['total_pemasukan'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="center" colspan="4">Tidak ada data paket</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="keep-together">
        <h2>Pemasukan Berdasarkan Tipe Pada Paket Pesanan</h2>
        <table class="main-table">
            <thead>
                <tr>
                    <th class="center">No</th>
                    <th class="center">Tipe Pesanan</th>
                    <th class="center">Jumlah Pesanan</th>
                    <th class="center">Total Pemasukan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ordersByType as $data)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td class="center">{{ $data['type'] }}</td>
                        <td class="center">{{ number_format($data['jumlah_pesanan']) }}</td>
                        <td class="right">Rp {{ number_format($data['total_pemasukan'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="center" colspan="4">Tidak ada data tipe</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</body>

</html>
