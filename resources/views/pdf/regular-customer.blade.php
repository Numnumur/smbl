<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $name }}</title>
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

        /* Column widths for the detail table (4 columns) */
        .main-table col:nth-child(1) {
            width: 8%;
        }

        /* No */
        .main-table col:nth-child(2) {
            width: 35%;
        }

        /* Nama Pelanggan */
        .main-table col:nth-child(3) {
            width: 27%;
        }

        /* Nomor WhatsApp */
        .main-table col:nth-child(4) {
            width: 30%;
        }

        /* Alamat */

        .section {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        /* Ensure table header and at least first few rows stay together */
        .force-together {
            page-break-inside: avoid;
            page-break-before: avoid;
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
            <td><strong>Kriteria Minimum Pesanan</strong></td>
            <td>:</td>
            <td>{{ number_format($minOrders) }} kali</td>
        </tr>
        <tr>
            <td><strong>Pelanggan Memenuhi Kriteria</strong></td>
            <td>:</td>
            <td>{{ number_format($qualifiedCustomers) }}</td>
        </tr>
    </table>

    <div class="force-together">
        <h2>Daftar Pelanggan Tetap</h2>
        <table class="main-table">
            <colgroup>
                <col style="width: 8%;">
                <col style="width: 35%;">
                <col style="width: 27%;">
                <col style="width: 30%;">
            </colgroup>
            <thead>
                <tr>
                    <th class="center">No</th>
                    <th class="center">Nama</th>
                    <th class="center">Nomor WhatsApp</th>
                    <th class="center">Alamat</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Table body as separate element to allow breaking if needed -->
    <table class="main-table" style="margin-top: 0;">
        <colgroup>
            <col style="width: 8%;">
            <col style="width: 35%;">
            <col style="width: 27%;">
            <col style="width: 30%;">
        </colgroup>
        <tbody>
            @forelse($customers as $index => $customer)
                <tr>
                    <td class="center number-cell">{{ $index + 1 }}</td>
                    <td class="left customer-name">{{ $customer['name'] }}</td>
                    <td class="left">{{ $customer['whatsapp'] }}</td>
                    <td class="left">{{ $customer['address'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="center">Tidak ada data pelanggan tetap</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
