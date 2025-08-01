<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pelanggan Tetap</title>
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

        .statistic-table td:nth-child(2) {
            width: 1px;
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
            <td><strong>Total Pelanggan</strong></td>
            <td>:</td>
            <td>{{ $totalCustomers }}</td>
        </tr>
        <tr>
            <td><strong>Kriteria Minimal Pesanan</strong></td>
            <td>:</td>
            <td>{{ $minOrders }} kali</td>
        </tr>
        <tr>
            <td><strong>Pelanggan yang Memenuhi Kriteria</strong></td>
            <td>:</td>
            <td>{{ $qualifiedCustomers }}</td>
        </tr>
    </table>

    <div class="section">
        <h2>Daftar Pelanggan Tetap</h2>
        <table>
            <thead>
                <tr>
                    <th class="center">No</th>
                    <th>Nama</th>
                    <th>Nomor WhatsApp</th>
                    <th>Alamat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $index => $data)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $data['name'] }}</td>
                        <td>{{ $data['whatsapp'] }}</td>
                        <td>{{ $data['address'] }}</td>
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
