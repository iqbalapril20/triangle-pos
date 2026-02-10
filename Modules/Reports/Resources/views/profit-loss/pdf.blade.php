<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        .periode {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
        }

        th {
            background: #f0f0f0;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>

    <h3>Laporan</h3>
    <p>Periode: {{ $start }} s/d {{ $end }}</p>

    {{-- A. PENJUALAN --}}
    <h4 style="margin: 14px 0 6px;">A. Penjualan</h4>
    <table width="100%" border="1" cellspacing="0" cellpadding="6" style="border-collapse: collapse;">
        <tr>
            <th>Keterangan</th>
            <th class="right">Jumlah</th>
        </tr>
        <tr>
            <td>Total Penjualan ({{ $data['penjualan']['trx_count'] ?? 0 }} transaksi)</td>
            <td class="right">{{ format_currency($data['penjualan']['total_sales']) }}</td>
        </tr>
    </table>

    {{-- B. KEUNTUNGAN --}}
    <h4 style="margin: 14px 0 6px;">B. Keuntungan</h4>
    <table width="100%" border="1" cellspacing="0" cellpadding="6" style="border-collapse: collapse;">
        <tr>
            <th>Keterangan</th>
            <th class="right">Jumlah</th>
        </tr>
        <tr>
            <td>Keuntungan</td>
            <td class="right">{{ format_currency($data['keuntungan']['profit'] ?? ($data['profit_amount'] ?? 0)) }}
            </td>
        </tr>
    </table>

    {{-- C. PENGELUARAN --}}
    <h4 style="margin: 14px 0 6px;">C. Pengeluaran</h4>
    <table width="100%" border="1" cellspacing="0" cellpadding="6" style="border-collapse: collapse;">
        <tr>
            <th>Keterangan</th>
            <th class="right">Jumlah</th>
        </tr>
        <tr>
            <td>Pembelian ({{ $data['pembelian']['trx_count'] ?? 0 }} transaksi)</td>
            <td class="right">{{ format_currency($data['pembelian']['total']) }}</td>
        </tr>
        <tr>
            <td>Biaya Operasional</td>
            <td class="right">{{ format_currency($data['biaya']['total']) }}</td>
        </tr>
    </table>

    {{-- D. KAS --}}
    <h4 style="margin: 14px 0 6px;">D. Kas</h4>
    <table width="100%" border="1" cellspacing="0" cellpadding="6" style="border-collapse: collapse;">
        <tr>
            <th>Keterangan</th>
            <th class="right">Jumlah</th>
        </tr>
        <tr>
            <td>Uang Masuk</td>
            <td class="right">{{ format_currency($data['kas']['uang_masuk']) }}</td>
        </tr>
        <tr>
            <td>Uang Keluar</td>
            <td class="right">{{ format_currency($data['kas']['uang_keluar']) }}</td>
        </tr>
        <tr>
            <td><b>Selisih Kas (Uang Masuk - Uang Keluar)</b></td>
            <td class="right"><b>{{ format_currency($data['kas']['selisih_kas']) }}</b></td>
        </tr>
    </table>


</body>

</html>
