<?php

namespace Modules\Product\Http\Controllers;

use Modules\Product\DataTables\ProductDataTable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use Modules\Upload\Entities\Upload;

class ProductController extends Controller
{

    public function index(ProductDataTable $dataTable)
    {
        abort_if(Gate::denies('access_products'), 403);

        return $dataTable->render('product::products.index');
    }


    public function create()
    {
        abort_if(Gate::denies('create_products'), 403);

        return view('product::products.create');
    }


    public function store(StoreProductRequest $request)
    {
        $data = $request->except(['document', 'product_code']); // <-- product_code jangan dari input

        // Convert format Rupiah ke angka murni
        $data['product_cost']  = unformat_idr($data['product_cost'] ?? null);
        $data['product_price'] = unformat_idr($data['product_price'] ?? null);

        $product = DB::transaction(function () use ($data, $request) {
            // pastikan category_id ada di request
            $categoryId = $data['category_id'];

            // Lock kategori biar aman kalau ada create produk barengan
            $category = Category::where('id', $categoryId)->lockForUpdate()->firstOrFail();

            $prefix = strtoupper($category->category_code); // dari tabel categories kamu

            // Ambil product_code terakhir dalam kategori ini
            $lastCode = Product::where('category_id', $categoryId)
                ->whereNotNull('product_code')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->value('product_code');

            $lastNumber = 0;

            if ($lastCode) {
                // contoh: ABC-0012 => ambil 0012
                if (preg_match('/(\d+)$/', $lastCode, $m)) {
                    $lastNumber = (int) $m[1];
                }
            }

            $nextNumber = $lastNumber + 1;
            $data['product_code'] = $prefix . '-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

            return Product::create($data);
        });

        if ($request->has('document')) {
            foreach ($request->input('document', []) as $file) {
                $product->addMedia(Storage::path('temp/dropzone/' . $file))
                    ->toMediaCollection('images');
            }
        }

        toast('Product Created!', 'success');

        return redirect()->route('products.index');
    }


    public function show(Product $product)
    {
        abort_if(Gate::denies('show_products'), 403);

        return view('product::products.show', compact('product'));
    }


    public function edit(Product $product)
    {
        abort_if(Gate::denies('edit_products'), 403);

        return view('product::products.edit', compact('product'));
    }


    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->except('document');

        // Convert format Rupiah ke angka murni
        $data['product_cost']  = unformat_idr($data['product_cost'] ?? null);
        $data['product_price'] = unformat_idr($data['product_price'] ?? null);

        $product->update($data);

        if ($request->has('document')) {
            if (count($product->getMedia('images')) > 0) {
                foreach ($product->getMedia('images') as $media) {
                    if (!in_array($media->file_name, $request->input('document', []))) {
                        $media->delete();
                    }
                }
            }

            $media = $product->getMedia('images')->pluck('file_name')->toArray();

            foreach ($request->input('document', []) as $file) {
                if (count($media) === 0 || !in_array($file, $media)) {
                    $product->addMedia(Storage::path('temp/dropzone/' . $file))->toMediaCollection('images');
                }
            }
        }

        toast('Product Updated!', 'info');

        return redirect()->route('products.index');
    }


    public function destroy(Product $product)
    {
        abort_if(Gate::denies('delete_products'), 403);

        $product->delete();

        toast('Product Deleted!', 'warning');

        return redirect()->route('products.index');
    }
    public function nextCode(Request $request)
    {
        $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $category = Category::findOrFail($request->category_id);
        $prefix = strtoupper($category->category_code);

        // ambil kode terakhir dalam kategori tsb
        $lastCode = Product::where('category_id', $category->id)
            ->whereNotNull('product_code')
            ->orderBy('id', 'desc')
            ->value('product_code');

        $lastNumber = 0;
        if ($lastCode && preg_match('/(\d+)$/', $lastCode, $m)) {
            $lastNumber = (int) $m[1];
        }

        $nextNumber = $lastNumber + 1;
        $code = $prefix . '-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'code' => $code,
        ]);
    }
}
