<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 40px;
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 20px;
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
        }

        .statistic-table td {
            padding: 2px 6px;
            vertical-align: top;
            white-space: nowrap;
            border: none;
        }

        .statistic-table td:nth-child(1) {
            width: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 12px;
        }

        th {
            text-align: center;
            background-color: #f0f0f0;
        }

        td.center,
        th.center {
            text-align: center;
        }

        .section {
            margin-top: 50px;
        }
    </style>
</head>

<body>
    <h1>{{ $name }}</h1>
    <br><br>
    <table class="statistic-table">
        <tr>
            <td><strong>Periode</strong></td>
            <td>:</td>
            <td>{{ $startDate }} - {{ $endDate }} ({{ $totalDays }} hari)</td>
        </tr>
        <tr>
            <td><strong>Total Pengeluaran</strong></td>
            <td>:</td>
            <td>Rp. {{ number_format($total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Rata-rata Per Hari</strong></td>
            <td>:</td>
            <td>Rp. {{ number_format($averagePerDay, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Rata-rata Per Transaksi</strong></td>
            <td>:</td>
            <td>Rp. {{ number_format($averagePerTransaction, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Hari Tertinggi</strong></td>
            <td>:</td>
            <td>{{ $topDay ? $topDay . ' (Rp. ' . number_format($topDayAmount, 0, ',', '.') . ')' : '-' }}</td>
        </tr>
    </table>

    <div class="section">
        <h2>List Pengeluaran Berdasarkan Kebutuhan</h2>
        <table>
            <thead>
                <tr>
                    <th class="center">No</th>
                    <th class="center">Kebutuhan</th>
                    <th class="center">Jumlah Transaksi</th>
                    <th class="center">Total Pengeluaran</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expensesByNeeds as $index => $data)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $data->needs ?? '-' }}</td>
                        <td class="center">{{ $data->jumlah_transaksi }}</td>
                        <td>Rp. {{ number_format($data->total_pengeluaran, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="center" colspan="4">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
