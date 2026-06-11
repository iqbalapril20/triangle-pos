@extends('layouts.app')

@section('title', 'Tambah Pengeluaran')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Pengeluaran</a></li>
        <li class="breadcrumb-item active">Tambah</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <form id="expense-form" action="{{ route('expenses.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    @include('utils.alerts')
                    <div class="form-group">
                        <button class="btn btn-primary">
                            Simpan Pengeluaran <i class="bi bi-check"></i>
                        </button>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="reference">Kode Pengeluaran <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="reference" required readonly
                                            value="EXP">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="date">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="date" required
                                            value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="category_id">Kategori <span class="text-danger">*</span></label>
                                        <select name="category_id" id="category_id" class="form-control" required>
                                            <option value="" selected>Pilih Kategori</option>
                                            @foreach (\Modules\Expense\Entities\ExpenseCategory::all() as $category)
                                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="amount">Jumlah <span class="text-danger">*</span></label>
                                        <input id="amount" type="text" class="form-control" name="amount" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="details">Keterangan</label>
                                <textarea class="form-control" rows="6" name="details"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
{{-- @push('page_scripts')
    <script src="{{ asset('js/jquery-mask-money.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#amount').maskMoney({
                prefix: '{{ settings()->currency->symbol }}',
                thousands: '{{ settings()->currency->thousand_separator }}',
                decimal: '{{ settings()->currency->decimal_separator }}',
                precision: {{ settings()->currency->code === 'IDR' ? 0 : 2 }},
            });

            $('#expense-form').submit(function() {
                var amount = $('#amount').maskMoney('unmasked')[0];
                $('#amount').val(amount);
            });
        });
    </script>
@endpush --}}
@push('page_scripts')
    <script src="{{ asset('js/jquery-mask-money.js') }}"></script>
    <script>
        $(function() {
            if (typeof $.fn.maskMoney === 'undefined') {
                console.error('maskMoney belum ter-load');
                return;
            }

            // 1) format tampilan amount
            $('#amount').maskMoney({
                prefix: '{{ settings()->currency->symbol }} ',
                thousands: '{{ settings()->currency->thousand_separator }}', // untuk IDR biasanya "."
                decimal: '{{ settings()->currency->decimal_separator }}', // untuk IDR biasanya ","
                precision: {{ settings()->currency->code === 'IDR' ? 0 : 2 }},
                allowZero: true
            });

            // 2) supaya value awal dari DB langsung ke-mask (jadi 7.950.000)
            $('#amount').maskMoney('mask');

            // 3) tombol isi sesuai total (kalau ada tombolnya)
            // $('#getTotalAmount').on('click', function() {
            //     // penting: ambil total tanpa desimal biar gak jadi 100x
            //     $('#amount').maskMoney('mask', {{ Cart::instance('expense')->total(0, '', '') }});
            // });

            // 4) sebelum submit, ubah jadi angka mentah (tanpa Rp / titik)
            $('#expense-form').on('submit', function() {
                // ambil angka saja: "Rp 7.950.000" -> "7950000"
                const raw = ($('#amount').val() || '').replace(/[^0-9]/g, '');
                $('#amount').val(raw || 0);
            });

        });
    </script>
@endpush
