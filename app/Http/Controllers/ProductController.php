<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['search' => ['nullable','string','max:100'], 'category_id' => ['nullable','integer','exists:categories,id'], 'sort' => ['nullable','in:name,sku,selling_price,stock,created_at'], 'direction' => ['nullable','in:asc,desc']]);
        $sort = $filters['sort'] ?? 'name';
        $direction = $filters['direction'] ?? 'asc';
        $products = Product::with(['category', 'suppliers', 'supplier'])
            ->when($request->filled('search'), fn($q) => $q->where(fn($sub) => $sub
                ->where('name', 'like', '%'.$request->search.'%')->orWhere('sku', 'like', '%'.$request->search.'%')))
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->integer('category_id')))
            ->orderBy($sort, $direction)->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();
        return view('products.index', compact('products', 'categories', 'sort', 'direction'));
    }
    public function create()
    {
        return view('products.form', ['product' => new Product(), 'categories' => Category::orderBy('name')->get(), 'suppliers' => Supplier::orderBy('name')->get()]);
    }
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $supplierIds = $data['supplier_ids'] ?? [];
        unset($data['supplier_ids']);
        $data['price'] = $data['selling_price'];
        DB::transaction(function () use ($data, $supplierIds, $request) {
            $data['supplier_id'] = $supplierIds[0] ?? null;
            $product = Product::create($data);
            $product->suppliers()->sync($supplierIds);
            if ($product->stock > 0) StockMovement::create(['product_id'=>$product->id,'user_id'=>$request->user()->id,'type'=>'IN','quantity'=>$product->stock,'note'=>'Saldo awal','created_at'=>now()]);
        });
        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }
    public function show(Product $product)
    {
        $product->load(['category', 'suppliers', 'supplier']);
        if (auth()->user()->isAdmin()) $product->load('stockMovements.user');
        else $product->load(['stockMovements' => fn($q) => $q->where('user_id', auth()->id())->with('user')]);
        return view('products.show', compact('product'));
    }
    public function edit(Product $product)
    {
        $product->load('suppliers');
        return view('products.form', ['product' => $product, 'categories' => Category::orderBy('name')->get(), 'suppliers' => Supplier::orderBy('name')->get()]);
    }
    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);
        $supplierIds = $data['supplier_ids'] ?? [];
        unset($data['supplier_ids'], $data['stock']);
        $data['price'] = $data['selling_price'];
        DB::transaction(function () use ($data, $supplierIds, $product) {
            $data['supplier_id'] = $supplierIds[0] ?? null;
            $product->update($data);
            $product->suppliers()->sync($supplierIds);
        });
        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }
    public function destroy(Product $product)
    {
        if ($product->stockMovements()->exists() || $product->purchaseItems()->exists() || $product->saleItems()->exists()) {
            return back()->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi stok.');
        }
        $product->suppliers()->detach();
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
    private function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['integer', 'distinct', 'exists:suppliers,id'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'stock' => [$product ? 'nullable' : 'required', 'integer', 'min:0'],
        ]);
    }
}
