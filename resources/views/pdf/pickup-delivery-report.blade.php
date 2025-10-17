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

        .detail-table {
            margin-top: 30px;
            page-break-inside: auto;
        }

        /* Allow detail table to break if too long */
        .detail-table tbody tr {
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
            <td><strong>Total Permintaan</strong></td>
            <td>:</td>
            <td>{{ number_format($totalRequests) }}</td>
        </tr>
        <tr>
            <td><strong>Total Pelanggan</strong></td>
            <td>:</td>
            <td>{{ number_format($totalCustomers) }}</td>
        </tr>
        <tr>
            <td><strong>Total Jenis Permintaan</strong></td>
            <td>:</td>
            <td>{{ number_format($totalTypes) }}</td>
        </tr>
        <tr>
            <td><strong>Jenis Terpopuler</strong></td>
            <td>:</td>
            <td>
                @if ($jenisTerpopuler)
                    {{ $jenisTerpopuler['type'] }} ({{ number_format($jenisTerpopuler['count']) }} permintaan)
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Permintaan Terbanyak</strong></td>
            <td>:</td>
            <td>
                @if ($pelangganTerpopuler)
                    {{ $pelangganTerpopuler['name'] }} ({{ number_format($pelangganTerpopuler['count']) }} permintaan)
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <div class="keep-together">
        <h2>Permintaan Berdasarkan Jenis</h2>
        <table class="main-table">
            <thead>
                <tr>
                    <th class="center">No</th>
                    <th class="center">Jenis Permintaan</th>
                    <th class="center">Jumlah Permintaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requestsByType as $type => $count)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td class="center">{{ $type }}</td>
                        <td class="center">{{ number_format($count) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="keep-together">
        <h2>Permintaan Berdasarkan Pelanggan</h2>
        <table class="main-table">
            <thead>
                <tr>
                    <th class="center">No</th>
                    <th class="center">Nama Pelanggan</th>
                    <th class="center">Total Permintaan</th>
                    <th class="center">Detail Permintaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requestsByCustomer as $name => $data)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td class="left">{{ $name }}</td>
                        <td class="center">{{ number_format($data['total']) }}</td>
                        <td class="left">
                            @foreach ($data['detail'] as $type => $count)
                                {{ $type }} ({{ number_format($count) }})@if (!$loop->last)
                                    <br>
                                @endif
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
