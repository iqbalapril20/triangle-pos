<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice Penjualan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .pb-10 {
            padding-bottom: 10px;
        }

        .border-bottom {
            border-bottom: 1px solid #dddddd;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-data th,
        .table-data td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .table-data th {
            background-color: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 3px 6px;
            font-size: 9px;
            color: #fff;
            background-color: #28a745;
            border-radius: 3px;
        }

        /* Grid table untuk Header Info */
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            vertical-align: top;
            width: 33.33%;
            padding-right: 15px;
        }
    </style>
</head>

<body>

    <div class="text-center mb-20">
        <img width="160" src="{{ public_path('images/logo-dark.png') }}" alt="Logo">
        <h3 class="mb-10">
            <span>Reference:</span> <strong>{{ $sale->reference }}</strong>
        </h3>
    </div>

    <!-- Info Company, Customer, & Invoice (Pakai Table 3 Kolom agar rata untuk DomPDF) -->
    <table class="info-table">
        <tr>
            <td>
                <h4 class="mb-10 border-bottom pb-10">Info Toko/Usaha:</h4>
                <div class="font-weight-bold mb-5">{{ settings()->company_name }}</div>
                <div>{{ settings()->company_address }}</div>
                <div>Email: {{ settings()->company_email }}</div>
                <div>Telepon: {{ settings()->company_phone }}</div>
            </td>
            <td>
                <h4 class="mb-10 border-bottom pb-10">Info Pelanggan:</h4>
                <div class="font-weight-bold mb-5">{{ optional($customer)->customer_name ?? $sale->customer_name }}</div>
                <div>{{ optional($customer)->address ?? '-' }}</div>
                <div>Email: {{ optional($customer)->customer_email ?? '-' }}</div>
                <div>Telepon: {{ optional($customer)->customer_phone ?? '-' }}</div>
            </td>
            <td>
                <h4 class="mb-10 border-bottom pb-10">Info Invoice:</h4>
                <div>Invoice: <strong>INV/{{ $sale->reference }}</strong></div>
                <div>Tanggal: {{ \Carbon\Carbon::parse($sale->date)->format('d M, Y') }}</div>
                <div>Status: <strong>{{ $sale->status }}</strong></div>
                <div>Pembayaran: <strong>{{ $sale->payment_status }}</strong></div>
            </td>
        </tr>
    </table>

    <div class="mt-20">
        <table class="table-data">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-right">Diskon</th>
                    <th class="text-right">Pajak</th>
                    <th class="text-right">Sub Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleDetails as $item)
                <tr>
                    <td>
                        {{ $item->product_name }} <br><br>
                        <span class="badge">{{ $item->product_code }}</span>
                    </td>
                    <td class="text-right">{{ format_currency($item->unit_price) }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ format_currency($item->product_discount_amount) }}</td>
                    <td class="text-right">{{ format_currency($item->product_tax_amount) }}</td>
                    <td class="text-right">{{ format_currency($item->sub_total) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Ringkasan Total (Di kanan bawah) -->
    <div class="mt-20">
        <table style="width: 40%; float: right;">
            <tbody>
                <tr>
                    <td><strong>Diskon ({{ $sale->discount_percentage }}%)</strong></td>
                    <td class="text-right">{{ format_currency($sale->discount_amount) }}</td>
                </tr>
                <tr>
                    <td><strong>Pajak ({{ $sale->tax_percentage }}%)</strong></td>
                    <td class="text-right">{{ format_currency($sale->tax_amount) }}</td>
                </tr>
                <tr>
                    <td><strong>Ongkos Kirim</strong></td>
                    <td class="text-right">{{ format_currency($sale->shipping_amount) }}</td>
                </tr>
                <tr>
                    <td class="border-bottom"><strong>Total Tagihan</strong></td>
                    <td class="text-right border-bottom font-weight-bold">{{ format_currency($sale->total_amount) }}</td>
                </tr>
            </tbody>
        </table>
        <div style="clear: both;"></div>
    </div>

    <!-- Footer -->
    <div class="text-center mt-20" style="font-style: italic; font-size: 10px; margin-top: 40px;">
        {{ settings()->company_name }} &copy; {{ date('Y') }}.
    </div>

</body>

</html>