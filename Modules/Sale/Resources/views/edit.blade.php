@extends('layouts.app')

@section('title', 'Edit Penjualan')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Penjualan</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid mb-4">
        <div class="row">
            <div class="col-12">
                <livewire:search-product />
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        @include('utils.alerts')
                        <form id="sale-form" action="{{ route('sales.update', $sale) }}" method="POST">
                            @csrf
                            @method('patch')

                            <div class="form-row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="reference">No. Transaksi <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="reference" required
                                            value="{{ $sale->reference }}" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="customer_id">Pelanggan <span class="text-danger">*</span></label>
                                        <select class="form-control" name="customer_id" id="customer_id">
                                            <option value="" selected>— Tanpa Pelanggan (Umum) —</option>
                                            @foreach (\Modules\People\Entities\Customer::all() as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="date">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="date" required
                                            value="{{ $sale->date }}">
                                    </div>
                                </div>
                            </div>

                            <livewire:product-cart :cartInstance="'sale'" :data="$sale" />

                            <div class="form-row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="status">Status <span class="text-danger">*</span></label>
                                        <select class="form-control" name="status" id="status" required>
                                            <option {{ $sale->status == 'Pending' ? 'selected' : '' }} value="Pending">
                                                Menunggu</option>
                                            <option {{ $sale->status == 'Shipped' ? 'selected' : '' }} value="Shipped">
                                                Dikirim</option>
                                            <option {{ $sale->status == 'Completed' ? 'selected' : '' }} value="Completed">
                                                Selesai</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="payment_method">Metode Pembayaran <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="payment_method" required
                                            value="{{ $sale->payment_method }}" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="paid_amount">Uang Diterima <span class="text-danger">*</span></label>
                                        <input id="paid_amount" type="text" class="form-control" name="paid_amount"
                                            required value="{{ $sale->paid_amount }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="note">Catatan (Jika Perlu)</label>
                                <textarea name="note" id="note" rows="5" class="form-control">{{ $sale->note }}</textarea>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    Simpan Perubahan <i class="bi bi-check"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
    <script src="{{ asset('js/jquery-mask-money.js') }}"></script>
    <script>
        $(function() {
            if (typeof $.fn.maskMoney === 'undefined') {
                console.error('maskMoney belum ter-load');
                return;
            }

            // 1) format tampilan paid_amount
            $('#paid_amount').maskMoney({
                prefix: '{{ settings()->currency->symbol }} ',
                thousands: '{{ settings()->currency->thousand_separator }}', // untuk IDR biasanya "."
                decimal: '{{ settings()->currency->decimal_separator }}', // untuk IDR biasanya ","
                precision: {{ settings()->currency->code === 'IDR' ? 0 : 2 }},
                allowZero: true
            });

            // 2) supaya value awal dari DB langsung ke-mask (jadi 7.950.000)
            $('#paid_amount').maskMoney('mask');

            // 3) tombol isi sesuai total (kalau ada tombolnya)
            $('#getTotalAmount').on('click', function() {
                // penting: ambil total tanpa desimal biar gak jadi 100x
                $('#paid_amount').maskMoney('mask', {{ Cart::instance('sale')->total(0, '', '') }});
            });

            // 4) sebelum submit, ubah jadi angka mentah (tanpa Rp / titik)
            $('#sale-form').on('submit', function() {
                // ambil angka saja: "Rp 7.950.000" -> "7950000"
                const raw = ($('#paid_amount').val() || '').replace(/[^0-9]/g, '');
                $('#paid_amount').val(raw || 0);
            });

        });
    </script>
@endpush
