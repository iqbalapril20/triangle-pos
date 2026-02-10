@extends('layouts.app')

@section('title', 'Edit Produk')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produk</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid mb-4">
        <form id="product-form" action="{{ route('products.update', $product->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('patch')

            <div class="row">
                <div class="col-lg-12">
                    @include('utils.alerts')
                    <div class="form-group">
                        <button class="btn btn-primary">Perbarui Produk <i class="bi bi-check"></i></button>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label for="product_name">Nama Produk <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="product_name" required
                                            value="{{ $product->product_name }}">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="product_code">Kode <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="product_code" required
                                            value="{{ $product->product_code }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category_id">Kategori <span class="text-danger">*</span></label>
                                        <select class="form-control" name="category_id" id="category_id" required>
                                            @foreach (\Modules\Product\Entities\Category::all() as $category)
                                                <option {{ $category->id == $product->category->id ? 'selected' : '' }}
                                                    value="{{ $category->id }}">{{ $category->category_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="barcode_symbology">Jenis Barcode <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" name="product_barcode_symbology" id="barcode_symbology"
                                            required>
                                            <option {{ $product->product_barcode_symbology == 'C128' ? 'selected' : '' }}
                                                value="C128">Code 128</option>
                                            <option {{ $product->product_barcode_symbology == 'C39' ? 'selected' : '' }}
                                                value="C39">Code 39</option>
                                            <option {{ $product->product_barcode_symbology == 'UPCA' ? 'selected' : '' }}
                                                value="UPCA">UPC-A</option>
                                            <option {{ $product->product_barcode_symbology == 'UPCE' ? 'selected' : '' }}
                                                value="UPCE">UPC-E</option>
                                            <option {{ $product->product_barcode_symbology == 'EAN13' ? 'selected' : '' }}
                                                value="EAN13">EAN-13</option>
                                            <option {{ $product->product_barcode_symbology == 'EAN8' ? 'selected' : '' }}
                                                value="EAN8">EAN-8</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Harga Modal <span class="text-danger">*</span></label>
                                        <input type="text" id="product_cost" name="product_cost" class="form-control idr"
                                            placeholder="Contoh: 12.000.000" required value="{{ $product->product_cost }}  ">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Markup (%)</label>
                                        <input type="number" id="markup_percent" class="form-control" step="0.01"
                                            placeholder="Contoh: 20">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Harga Jual <span class="text-danger">*</span></label>
                                        <input type="text" id="product_price" name="product_price"
                                            class="form-control idr" placeholder="Contoh: 15.000.000" required
                                            value="{{ $product->product_price }}">
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_cost">Harga Modal <span class="text-danger">*</span></label>
                                        <input type="text" id="product_cost" name="product_cost" class="form-control idr"
                                            placeholder="Contoh: 12.000.000" required value="{{ $product->product_cost }}">
                                        <small class="text-muted">Contoh: 25.000.000</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_price">Harga Jual <span class="text-danger">*</span></label>
                                        <input type="text" id="product_price" name="product_price"
                                            class="form-control idr" placeholder="Contoh: 15.000.000" required
                                            value="{{ $product->product_price }}">
                                        <small class="text-muted">Contoh: 30.000.000</small>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_quantity">Jumlah Stok <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="product_quantity" required
                                            value="{{ $product->product_quantity }}" min="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_stock_alert">Batas Peringatan Stok <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="product_stock_alert" required
                                            value="{{ $product->product_stock_alert }}" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="product_order_tax">Pajak (%)</label>
                                        <input type="number" class="form-control" name="product_order_tax"
                                            value="{{ $product->product_order_tax }}" min="0" max="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="product_tax_type">Jenis Pajak</label>
                                        <select class="form-control" name="product_tax_type" id="product_tax_type">
                                            <option value=""
                                                {{ empty($product->product_tax_type) ? 'selected' : '' }}>Tidak ada
                                            </option>
                                            <option {{ $product->product_tax_type == 1 ? 'selected' : '' }}
                                                value="1">Eksklusif</option>
                                            <option {{ $product->product_tax_type == 2 ? 'selected' : '' }}
                                                value="2">Inklusif</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="product_unit">
                                            Satuan
                                            <i class="bi bi-question-circle-fill text-info" data-toggle="tooltip"
                                                data-placement="top"
                                                title="Teks singkat ini akan ditampilkan setelah jumlah produk."></i>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" name="product_unit" id="product_unit" required>
                                            <option value="" selected>Pilih Satuan</option>
                                            @foreach (\Modules\Setting\Entities\Unit::all() as $unit)
                                                <option {{ $product->product_unit == $unit->short_name ? 'selected' : '' }}
                                                    value="{{ $unit->short_name }}">
                                                    {{ $unit->name . ' | ' . $unit->short_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="product_note">Catatan</label>
                                <textarea name="product_note" id="product_note" rows="4" class="form-control">{{ $product->product_note }}</textarea>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="image">
                                    Gambar Produk
                                    <i class="bi bi-question-circle-fill text-info" data-toggle="tooltip"
                                        data-placement="top"
                                        title="Maksimal 3 file, ukuran maksimal 1MB, ukuran gambar 400x400"></i>
                                </label>
                                <div class="dropzone d-flex flex-wrap align-items-center justify-content-center"
                                    id="document-dropzone">
                                    <div class="dz-message" data-dz-message>
                                        <i class="bi bi-cloud-arrow-up"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
@endsection

@section('third_party_scripts')
    <script src="{{ asset('js/dropzone.js') }}"></script>
@endsection

@push('page_scripts')
    <script>
        var uploadedDocumentMap = {}
        Dropzone.options.documentDropzone = {
            url: '{{ route('dropzone.upload') }}',
            maxFilesize: 1,
            acceptedFiles: '.jpg, .jpeg, .png',
            maxFiles: 3,
            addRemoveLinks: true,
            dictRemoveFile: "<i class='bi bi-x-circle text-danger'></i> remove",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function(file, response) {
                $('form').append('<input type="hidden" name="document[]" value="' + response.name + '">');
                uploadedDocumentMap[file.name] = response.name;
            },
            removedfile: function(file) {
                file.previewElement.remove();
                var name = '';
                if (typeof file.file_name !== 'undefined') {
                    name = file.file_name;
                } else {
                    name = uploadedDocumentMap[file.name];
                }
                $('form').find('input[name="document[]"][value="' + name + '"]').remove();
            },
            init: function() {
                @if (isset($product) && $product->getMedia('images'))
                    var files = {!! json_encode($product->getMedia('images')) !!};
                    for (var i in files) {
                        var file = files[i];
                        this.options.addedfile.call(this, file);
                        this.options.thumbnail.call(this, file, file.original_url);
                        file.previewElement.classList.add('dz-complete');
                        $('form').append('<input type="hidden" name="document[]" value="' + file.file_name + '">');
                    }
                @endif
            }
        }
    </script>

    <script>
        (function() {

            function formatIDR(value) {
                const digits = String(value || '').replace(/\D/g, '');
                if (!digits) return '';
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function toNumber(value) {
                return String(value || '').replace(/\D/g, '');
            }

            // Format saat mengetik
            document.addEventListener('input', function(e) {
                if (!e.target.classList.contains('idr')) return;

                const raw = e.target.value;
                e.target.value = formatIDR(raw);
            });

            // Format nilai awal (edit page)
            document.querySelectorAll('input.idr').forEach(function(el) {
                el.value = formatIDR(el.value);
            });

            // Sebelum submit → kirim angka murni
            const form = document.getElementById('product-form');
            if (form) {
                form.addEventListener('submit', function() {
                    const cost = document.getElementById('product_cost');
                    const price = document.getElementById('product_price');

                    if (cost) cost.value = toNumber(cost.value);
                    if (price) price.value = toNumber(price.value);
                });
            }

        })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const costInput = document.getElementById('product_cost');
            const priceInput = document.getElementById('product_price');
            const percentInput = document.getElementById('markup_percent');

            let fromPercent = false;
            let fromPrice = false;

            function parseIDR(value) {
                return parseFloat(value.replace(/[^0-9]/g, '')) || 0;
            }

            function formatIDR(value) {
                return value.toLocaleString('id-ID');
            }

            // === JIKA ISI PERSENTASE ===
            percentInput.addEventListener('input', function() {
                if (fromPrice) return;
                fromPercent = true;

                const cost = parseIDR(costInput.value);
                const percent = parseFloat(percentInput.value) || 0;

                if (cost > 0) {
                    const selling = cost + (cost * percent / 100);
                    priceInput.value = formatIDR(Math.round(selling));
                }

                fromPercent = false;
            });

            // === JIKA ISI HARGA JUAL ===
            priceInput.addEventListener('input', function() {
                if (fromPercent) return;
                fromPrice = true;

                const cost = parseIDR(costInput.value);
                const price = parseIDR(priceInput.value);

                if (cost > 0 && price >= cost) {
                    const percent = ((price - cost) / cost) * 100;
                    percentInput.value = percent.toFixed(2);
                } else {
                    percentInput.value = 0;
                }

                fromPrice = false;
            });

            // === JIKA HARGA MODAL DIUBAH ===
            costInput.addEventListener('input', function() {
                // recalc dari harga jual → persen
                const cost = parseIDR(costInput.value);
                const price = parseIDR(priceInput.value);

                if (cost > 0 && price >= cost) {
                    const percent = ((price - cost) / cost) * 100;
                    percentInput.value = percent.toFixed(2);
                }
            });
        });
    </script>
@endpush
