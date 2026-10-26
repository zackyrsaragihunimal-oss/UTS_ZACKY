<?php
namespace App\Http\Controllers;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class SupplierController extends Controller
{
    public function index() { $suppliers = Supplier::withCount('products')->orderBy('name')->paginate(10); return view('suppliers.index', compact('suppliers')); }
    public function create() { return view('suppliers.form', ['supplier' => new Supplier()]); }
    public function store(Request $request) { $data = $this->validated($request); Supplier::create($data); return redirect()->route('suppliers.index')->with('success','Pemasok ditambahkan.'); }
    public function show(Supplier $supplier) { $supplier->load('products'); return view('suppliers.show', compact('supplier')); }
    public function edit(Supplier $supplier) { return view('suppliers.form', compact('supplier')); }
    public function update(Request $request, Supplier $supplier) { $supplier->update($this->validated($request)); return redirect()->route('suppliers.index')->with('success','Pemasok diperbarui.'); }
    public function destroy(Supplier $supplier) { if ($supplier->products()->exists() || $supplier->primaryProducts()->exists() || $supplier->purchases()->exists()) return back()->with('error','Pemasok tidak dapat dihapus karena masih terhubung ke produk.'); $supplier->delete(); return redirect()->route('suppliers.index')->with('success','Pemasok dihapus.'); }
    private function validated(Request $request): array { return $request->validate(['name'=>['required','string','max:150'], 'phone'=>['nullable','string','max:30'], 'email'=>['nullable','email','max:150'], 'address'=>['nullable','string']]); }
}
