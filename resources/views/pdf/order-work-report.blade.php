<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pengerjaan Pesanan</title>
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
            <td><strong>Total Pesanan Masuk</strong></td>
            <td>:</td>
            <td>{{ number_format($totalPesananMasuk) }}</td>
        </tr>
        <tr>
            <td><strong>Total Pesanan Selesai</strong></td>
            <td>:</td>
            <td>{{ number_format($totalPesananSelesai) }}</td>
        </tr>
        <tr>
            <td><strong>Total Paket Pesanan</strong></td>
            <td>:</td>
            <td>{{ number_format($totalPaketPesanan) }}</td>
        </tr>
        <tr>
            <td><strong>Total Tipe Paket Pesanan</strong></td>
            <td>:</td>
            <td>{{ number_format($totalTipePaketPesanan) }}</td>
        </tr>
        <tr>
            <td><strong>Paket Terpopuler</strong></td>
            <td>:</td>
            <td>
                @if ($paketTerpopuler)
                    {{ $paketTerpopuler['package'] }} ({{ number_format($paketTerpopuler['jumlah_pesanan']) }} pesanan)
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Tipe Terpopuler</strong></td>
            <td>:</td>
            <td>
                @if ($tipeTerpopuler)
                    {{ $tipeTerpopuler['type'] }} ({{ number_format($tipeTerpopuler['jumlah_pesanan']) }} pesanan)
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <div class="keep-together">
        <h2>Pengerjaan Berdasarkan Paket Pesanan</h2>
        <table class="main-table">
            <thead>
                <tr>
                    <th class="center">No</th>
                    <th class="center">Nama Paket</th>
                    <th class="center">Tipe</th>
                    <th class="center">Total Pesanan</th>
                    <th class="center">Total Pengerjaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ordersByPackage as $data)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td class="left">{{ $data['package'] }}</td>
                        <td class="center">{{ $data['type'] }}</td>
                        <td class="center">{{ number_format($data['jumlah_pesanan']) }}</td>
                        <td class="left">
                            @if ($data['type'] === 'Karpet')
                                {!! nl2br(e($data['total_pengerjaan'])) !!}
                            @else
                                {{ number_format($data['total_pengerjaan']) }} {{ $data['unit'] }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="keep-together">
        <h2>Pengerjaan Berdasarkan Tipe Pada Paket Pesanan</h2>
        <table class="main-table">
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
                        <td class="center">{{ $data['type'] }}</td>
                        <td class="center">{{ number_format($data['jumlah_pesanan']) }}</td>
                        <td class="left">
                            @if ($data['type'] === 'Karpet')
                                {!! nl2br(e($data['total_pengerjaan'])) !!}
                            @else
                                {{ number_format($data['total_pengerjaan']) }} {{ $data['unit'] }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($ordersKarpetByUkuran->count() > 0)
        <div class="keep-together">
            <h2>Detail Ukuran Karpet</h2>
            <table class="main-table">
                <thead>
                    <tr>
                        <th class="center">No</th>
                        <th class="center">Ukuran Karpet</th>
                        <th class="center">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ordersKarpetByUkuran as $data)
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td class="center">{{ $data['ukuran'] }}</td>
                            <td class="center">{{ number_format($data['jumlah']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</body>

</html>
