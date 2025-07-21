<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pengerjaan Pesanan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 40px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
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
            margin-bottom: 30px;
            /* sudah tidak auto-center */
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

        .section {
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
            <td>{{ $startDate }} - {{ $endDate }}</td>
        </tr>
        <tr>
            <td><strong>Total Pesanan Masuk</strong></td>
            <td>:</td>
            <td>{{ $totalPesananMasuk }}</td>
        </tr>
        <tr>
            <td><strong>Total Pesanan Selesai</strong></td>
            <td>:</td>
            <td>{{ $totalPesananSelesai }}</td>
        </tr>
    </table>

    <div class="section">
        <h2>Pengerjaan Berdasarkan Paket Pesanan</h2>
        <table>
            <thead>
                <tr>
                    <th class="center">No</th>
                    <th class="center">Nama Paket</th>
                    <th class="center">Total Pesanan</th>
                    <th class="center">Total Pengerjaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ordersByPackage as $data)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $data['package'] }}</td>
                        <td class="center">{{ $data['jumlah_pesanan'] }}</td>
                        <td>{!! nl2br(e($data['total_pengerjaan'])) !!} {{ $data['unit'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Pengerjaan Berdasarkan Tipe Paket</h2>
        <table>
            <thead>
                <tr>
                    <th class="center">No</th>
                    <th class="center">Tipe Paket</th>
                    <th class="center">Total Pesanan</th>
                    <th class="center">Total Pengerjaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ordersByType as $data)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $data['type'] }}</td>
                        <td class="center">{{ $data['jumlah_pesanan'] }}</td>
                        <td>{!! nl2br(e($data['total_pengerjaan'])) !!} {{ $data['unit'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
