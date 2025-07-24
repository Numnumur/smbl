<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pemberian Diskon</title>
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
            background-color: #f0f0f0;
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .statistic-table {
            border-collapse: collapse;
            width: auto;
            font-size: 13px;
            border: none;
            margin-bottom: 30px;
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
            <td>{{ $startDate }} - {{ $endDate }} ({{ $totalDays }} hari)</td>
        </tr>
        <tr>
            <td><strong>Total Penggunaan Diskon</strong></td>
            <td>:</td>
            <td>{{ $totalUsage }} kali</td>
        </tr>
        <tr>
            <td><strong>Total Potongan Harga</strong></td>
            <td>:</td>
            <td>Rp. {{ number_format($totalDiscount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="section">
        <h2>Pemberian Diskon Berdasarkan Paket Pesanan</h2>
        <table>
            <thead>
                <tr>
                    <th class="center">No</th>
                    <th class="center">Nama Diskon</th>
                    <th class="center">Tipe</th>
                    <th class="center">Nilai</th>
                    <th class="center">Nama Paket</th>
                    <th class="center">Jumlah Penggunaan</th>
                    <th class="center">Total Potongan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($byPackage as $index => $item)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $item['discount'] }}</td>
                        <td class="center">{{ $item['type'] ?? '-' }}</td>
                        <td class="center">
                            @if ($item['type'] === 'Persentase')
                                {{ $item['value'] }}%
                            @elseif ($item['type'] === 'Langsung')
                                Rp. {{ number_format($item['value'], 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $item['package'] }}</td>
                        <td class="center">{{ $item['count'] }}</td>
                        <td>Rp. {{ number_format($item['total_value'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Pemberian Diskon Berdasarkan Tipe Pada Paket Pesanan</h2>
        <table>
            <thead>
                <tr>
                    <th class="center">No</th>
                    <th class="center">Tipe Pesanan</th>
                    <th class="center">Jumlah Penggunaan</th>
                    <th class="center">Total Potongan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($byType as $index => $item)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td class="center">{{ $item['count'] }}</td>
                        <td>Rp. {{ number_format($item['total_value'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
