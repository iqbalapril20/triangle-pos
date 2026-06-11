@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('breadcrumb')
<ol class="breadcrumb border-0 m-0">
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Penjualan</a></li>
    <li class="breadcrumb-item active">Detail</li>
</ol>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center">
                    <div>
                        No. Penjualan: <strong>{{ $sale->reference }}</strong>
                    </div>
                    <a target="_blank" class="btn btn-sm btn-secondary mfs-auto mfe-1 d-print-none" href="{{ route('sales.pos.pdf', $sale->id) }}">
                        <i class="bi bi-printer"></i> Cetak Struk POS
                    </a>
                    <a target="_blank" class="btn btn-sm btn-info mfe-1 d-print-none" href="{{ route('sales.pdf', $sale->id) }}">
                        <i class="bi bi-save"></i> Cetak Invoice
                    </a>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm-4 mb-3 mb-md-0">
                            <h5 class="mb-2 border-bottom pb-2">Info Toko/Usaha:</h5>
                            <div><strong>{{ settings()->company_name }}</strong></div>
                            <div>{{ settings()->company_address }}</div>
                            <div>Email: {{ settings()->company_email }}</div>
                            <div>Telepon: {{ settings()->company_phone }}</div>
                        </div>

                        <div class="col-sm-4 mb-3 mb-md-0">
                            <h5 class="mb-2 border-bottom pb-2">Info Pelanggan (Customer):</h5>
                            <div><strong>{{ optional($customer)->customer_name ?? $sale->customer_name ?? 'Umum' }}</strong></div>
                            <div>{{ optional($customer)->address ?? '-' }}</div>
                            <div>Email: {{ optional($customer)->customer_email ?? '-' }}</div>
                            <div>Telepon: {{ optional($customer)->customer_phone ?? '-' }}</div>
                        </div>

                        <div class="col-sm-4 mb-3 mb-md-0">
                            <h5 class="mb-2 border-bottom pb-2">Info Invoice:</h5>
                            <div>Invoice: <strong>INV/{{ $sale->reference }}</strong></div>
                            <div>Tanggal: {{ \Carbon\Carbon::parse($sale->date)->format('d M, Y') }}</div>
                            <div>
                                Status: <strong>{{ $sale->status }}</strong>
                            </div>
                            <div>
                                Status Pembayaran: <strong>{{ $sale->payment_status }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive-sm">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="align-middle">Produk</th>
                                    <th class="align-middle">Harga Satuan Bersih</th>
                                    <th class="align-middle">Jumlah</th>
                                    <th class="align-middle">Diskon</th>
                                    <th class="align-middle">Pajak</th>
                                    <th class="align-middle">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->saleDetails as $item)
                                <tr>
                                    <td class="align-middle">
                                        {{ $item->product_name }} <br>
                                        <span class="badge badge-success">
                                            {{ $item->product_code }}
                                        </span>
                                    </td>

                                    <td class="align-middle">{{ format_currency($item->unit_price) }}</td>

                                    <td class="align-middle">
                                        {{ $item->quantity }}
                                    </td>

                                    <td class="align-middle">
                                        {{ format_currency($item->product_discount_amount) }}
                                    </td>

                                    <td class="align-middle">
                                        {{ format_currency($item->product_tax_amount) }}
                                    </td>

                                    <td class="align-middle">
                                        {{ format_currency($item->sub_total) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 col-sm-5 ml-md-auto">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="left"><strong>Diskon ({{ $sale->discount_percentage }}%)</strong></td>
                                        <td class="right">{{ format_currency($sale->discount_amount) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="left"><strong>Pajak ({{ $sale->tax_percentage }}%)</strong></td>
                                        <td class="right">{{ format_currency($sale->tax_amount) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="left"><strong>Ongkir</strong></td>
                                        <td class="right">{{ format_currency($sale->shipping_amount) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="left"><strong>Total Tagihan</strong></td>
                                        <td class="right"><strong>{{ format_currency($sale->total_amount) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection