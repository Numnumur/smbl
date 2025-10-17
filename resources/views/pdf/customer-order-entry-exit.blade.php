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
            margin-bottom: 15px;
            font-size: 20px;
            margin-top: 0;
        }

        h2 {
            margin-top: 20px;
            margin-bottom: 8px;
            text-align: center;
            font-size: 16px;
            page-break-after: avoid;
        }

        .statistic-table {
            border-collapse: collapse;
            width: auto;
            font-size: 13px;
            border: none;
            margin-bottom: 20px;
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
            margin-top: 5px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
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

        /* Column widths for the detail table */
        .main-table col:nth-child(1) {
            width: 5%;
        }

        /* No */
        .main-table col:nth-child(2) {
            width: 20%;
        }

        /* Nama Pelanggan */
        .main-table col:nth-child(3) {
            width: 8%;
        }

        /* Masuk */
        .main-table col:nth-child(4) {
            width: 8%;
        }

        /* Keluar */
        .main-table col:nth-child(5) {
            width: 29.5%;
        }

        /* Detail Tanggal Masuk */
        .main-table col:nth-child(6) {
            width: 29.5%;
        }

        /* Detail Tanggal Keluar */

        .section {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        /* Ensure table header and at least first few rows stay together */
        .force-together {
            page-break-inside: avoid;
            page-break-before: avoid;
        }

        .date-list {
            font-size: 9px;
            line-height: 1.2;
        }

        .date-item {
            margin-bottom: 1px;
        }

        /* Customer name styling */
        .customer-name {
            font-size: 12px;
        }

        /* Number styling */
        .number-cell {
            font-size: 12px;
        }
    </style>
</head>

<body>
    <h1>{{ $name }}</h1>

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
            <td><strong>Total Pesanan Masuk</strong></td>
            <td>:</td>
            <td>{{ number_format($totalEntry) }}</td>
        </tr>
        <tr>
            <td><strong>Total Pesanan Keluar</strong></td>
            <td>:</td>
            <td>{{ number_format($totalExit) }}</td>
        </tr>
        <tr>
            <td><strong>Pelanggan Dengan Pesanan Masuk</strong></td>
            <td>:</td>
            <td>{{ number_format($totalCustomersWithEntry) }}</td>
        </tr>
        <tr>
            <td><strong>Pelanggan Dengan Pesanan Keluar</strong></td>
            <td>:</td>
            <td>{{ number_format($totalCustomersWithExit) }}</td>
        </tr>
        <tr>
            <td><strong>Pelanggan Terbanyak Masuk</strong></td>
            <td>:</td>
            <td>
                @if ($pelangganTerbanyakMasuk)
                    {{ $pelangganTerbanyakMasuk['name'] }} ({{ number_format($pelangganTerbanyakMasuk['entry']) }}
                    pesanan)
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Pelanggan Terbanyak Keluar</strong></td>
            <td>:</td>
            <td>
                @if ($pelangganTerbanyakKeluar)
                    {{ $pelangganTerbanyakKeluar['name'] }} ({{ number_format($pelangganTerbanyakKeluar['exit']) }}
                    pesanan)
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <div class="force-together">
        <h2>Detail Keluar Masuk Pesanan Per Pelanggan</h2>
        <table class="main-table">
            <colgroup>
                <col style="width: 5%;">
                <col style="width: 20%;">
                <col style="width: 8%;">
                <col style="width: 8%;">
                <col style="width: 29.5%;">
                <col style="width: 29.5%;">
            </colgroup>
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
        </table>
    </div>

    <!-- Table body as separate element to allow breaking if needed -->
    <table class="main-table" style="margin-top: 0;">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 20%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 29.5%;">
            <col style="width: 29.5%;">
        </colgroup>
        <tbody>
            @forelse($customers as $index => $customer)
                <tr>
                    <td class="center number-cell">{{ $index + 1 }}</td>
                    <td class="left customer-name">{{ $customer['name'] }}</td>
                    <td class="center number-cell">
                        @if ($customer['entry'] > 0)
                            {{ number_format($customer['entry']) }}
                        @else
                            0
                        @endif
                    </td>
                    <td class="center number-cell">
                        @if ($customer['exit'] > 0)
                            {{ number_format($customer['exit']) }}
                        @else
                            0
                        @endif
                    </td>
                    <td class="left">
                        @if (count($customer['entry_dates']) > 0)
                            <div class="date-list">
                                @foreach ($customer['entry_dates'] as $date)
                                    <div class="date-item">
                                        • {{ \Carbon\Carbon::parse($date)->translatedFormat('l, j F Y') }}
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="center">-</span>
                        @endif
                    </td>
                    <td class="left">
                        @if (count($customer['exit_dates']) > 0)
                            <div class="date-list">
                                @foreach ($customer['exit_dates'] as $date)
                                    <div class="date-item">
                                        • {{ \Carbon\Carbon::parse($date)->translatedFormat('l, j F Y') }}
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="center">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">Tidak ada data pelanggan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
