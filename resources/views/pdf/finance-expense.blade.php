<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - Pengeluaran</title>
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

        .keep-together {
            page-break-inside: avoid;
            margin-top: 50px;
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
            <td><strong>Total Pengeluaran</strong></td>
            <td>:</td>
            <td>Rp {{ number_format($total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Rata-rata Per Hari</strong></td>
            <td>:</td>
            <td>Rp {{ number_format($averagePerDay, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Rata-rata Per Transaksi</strong></td>
            <td>:</td>
            <td>Rp {{ number_format($averagePerTransaction, 0, ',', '.') }}</td>
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
        <h2>Pengeluaran Berdasarkan Kebutuhan</h2>
        <table class="main-table">
            <thead>
                <tr>
                    <th class="center">No</th>
                    <th class="center">Kebutuhan</th>
                    <th class="center">Jumlah Transaksi</th>
                    <th class="center">Total Pengeluaran</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expensesByNeeds as $data)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td class="left">{{ $data['needs'] }}</td>
                        <td class="center">{{ number_format($data['jumlah_transaksi']) }}</td>
                        <td class="right">Rp {{ number_format($data['total_pengeluaran'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="center" colspan="4">Tidak ada data kebutuhan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</body>

</html>
