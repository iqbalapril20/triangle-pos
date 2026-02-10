<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            font-size: 12px;
            line-height: 18px;
            font-family: monospace;
        }

        h2 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        tr {
            border-bottom: 1px dashed #ddd;
        }

        td, th {
            padding: 6px 0;
            vertical-align: top;
        }

        .centered {
            text-align: center;
        }

        @page {
            size: 80mm auto;
            margin: 5mm;
        }

        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>

@php
    // === HITUNG KEMBALIAN ===
    $change = 0;
    if ($sale->paid_amount > $sale->total_amount) {
        $change = $sale->paid_amount - $sale->total_amount;
    }
@endphp

<div style="max-width:400px;margin:0 auto">
    <div id="receipt-data">

        {{-- HEADER --}}
        <div class="centered">
            <h2>{{ settings()->company_name }}</h2>
            <div style="font-size:11px;line-height:15px;">
                {{ settings()->company_email }}, {{ settings()->company_phone }}<br>
                {{ settings()->company_address }}
            </div>
        </div>

        <hr>

        {{-- INFO TRANSAKSI --}}
        <p style="font-size:11px;">
            Tanggal : {{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}<br>
            No.     : {{ $sale->reference }}<br>
            Pelanggan : {{ $sale->customer_name }}
        </p>

        {{-- DETAIL BARANG --}}
        <table>
            <tbody>
                @foreach ($sale->saleDetails as $saleDetail)
                    <tr>
                        <td colspan="2">
                            {{ $saleDetail->product->product_name }}<br>
                            <small>{{ $saleDetail->quantity }} x {{ format_currency($saleDetail->price) }}</small>
                        </td>
                        <td style="text-align:right;">
                            {{ format_currency($saleDetail->sub_total) }}
                        </td>
                    </tr>
                @endforeach

                @if ($sale->tax_percentage)
                    <tr>
                        <td colspan="2">Pajak ({{ $sale->tax_percentage }}%)</td>
                        <td style="text-align:right;">{{ format_currency($sale->tax_amount) }}</td>
                    </tr>
                @endif

                @if ($sale->discount_percentage)
                    <tr>
                        <td colspan="2">Diskon ({{ $sale->discount_percentage }}%)</td>
                        <td style="text-align:right;">{{ format_currency($sale->discount_amount) }}</td>
                    </tr>
                @endif

                @if ($sale->shipping_amount)
                    <tr>
                        <td colspan="2">Ongkir</td>
                        <td style="text-align:right;">{{ format_currency($sale->shipping_amount) }}</td>
                    </tr>
                @endif

                <tr>
                    <th colspan="2">TOTAL</th>
                    <th style="text-align:right;">{{ format_currency($sale->total_amount) }}</th>
                </tr>
            </tbody>
        </table>

        <hr>

        {{-- PEMBAYARAN --}}
        <table>
            <tbody>
                <tr>
                    <td>Metode</td>
                    <td style="text-align:right;">{{ $sale->payment_method }}</td>
                </tr>
                <tr>
                    <td>Diterima</td>
                    <td style="text-align:right;">{{ format_currency($sale->paid_amount) }}</td>
                </tr>

                {{-- KEMBALIAN --}}
                @if ($change > 0)
                <tr>
                    <td><strong>Kembalian</strong></td>
                    <td style="text-align:right;"><strong>{{ format_currency($change) }}</strong></td>
                </tr>
                @endif
            </tbody>
        </table>

        {{-- BARCODE --}}
        <div class="centered" style="margin-top:10px;">
            {!! \Milon\Barcode\Facades\DNS1DFacade::getBarcodeSVG(
                $sale->reference,
                'C128',
                1,
                25,
                'black',
                false
            ) !!}
        </div>

        <div class="centered" style="margin-top:8px;font-size:11px;">
            Terima kasih
        </div>

    </div>
</div>

</body>
</html>
