<div>
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body">
            <livewire:pos.filter :categories="$categories"/>

            <div class="row position-relative">
                <div wire:loading.flex
                     class="col-12 position-absolute justify-content-center align-items-center"
                     style="top:0;right:0;left:0;bottom:0;background-color: rgba(255,255,255,0.5);z-index: 99;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Memuat...</span>
                    </div>
                </div>

                @forelse($products as $product)
                    @php $outOfStock = $product->product_quantity <= 0; @endphp
                    <div @if(!$outOfStock) wire:click.prevent="selectProduct({{ $product }})" @endif
                         class="col-lg-4 col-md-6 col-xl-3 mb-3"
                         style="cursor: {{ $outOfStock ? 'not-allowed' : 'pointer' }};">
                        <div class="card border-0 shadow h-100 {{ $outOfStock ? 'opacity-50' : '' }}">
                            <div class="position-relative">
                                <img height="200"
                                     src="{{ $product->getFirstMediaUrl('images') }}"
                                     class="card-img-top"
                                     alt="Gambar Produk">
                                <div class="badge {{ $outOfStock ? 'badge-danger' : 'badge-info' }} mb-3 position-absolute"
                                     style="left:10px;top: 10px;">
                                    {{ $outOfStock ? 'Stok Habis' : 'Stok: ' . $product->product_quantity }}
                                </div>
                                @if($outOfStock)
                                    <div class="position-absolute w-100 h-100 d-flex justify-content-center align-items-center"
                                         style="top:0;left:0;background:rgba(0,0,0,0.08);">
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <h6 style="font-size: 13px;" class="card-title mb-0">
                                        {{ $product->product_name }}
                                    </h6>
                                    <span class="badge badge-success">
                                        {{ $product->product_code }}
                                    </span>
                                </div>
                                <p class="card-text font-weight-bold">
                                    {{ format_currency($product->product_price) }}
                                </p>
                                @if($outOfStock)
                                    <small class="text-danger font-weight-bold">
                                        <i class="bi bi-exclamation-circle-fill"></i> Tidak tersedia
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-warning mb-0">
                            Produk Tidak Ditemukan
                        </div>
                    </div>
                @endforelse
            </div>

            <div @class(['mt-3' => $products->hasPages()])>
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
