<?php
namespace App\Http\Controllers;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class CategoryController extends Controller
{
    public function index() { $categories = Category::withCount('products')->orderBy('name')->paginate(10); return view('categories.index', compact('categories')); }
    public function create() { return view('categories.form', ['category' => new Category()]); }
    public function store(Request $request) { $data = $request->validate(['name' => ['required','string','max:100','unique:categories,name'], 'description' => ['nullable','string']]); Category::create($data); return redirect()->route('categories.index')->with('success','Kategori ditambahkan.'); }
    public function show(Category $category) { $category->load('products'); return view('categories.show', compact('category')); }
    public function edit(Category $category) { return view('categories.form', compact('category')); }
    public function update(Request $request, Category $category) { $data = $request->validate(['name' => ['required','string','max:100',Rule::unique('categories','name')->ignore($category->id)], 'description' => ['nullable','string']]); $category->update($data); return redirect()->route('categories.index')->with('success','Kategori diperbarui.'); }
    public function destroy(Category $category) { if ($category->products()->exists()) return back()->with('error','Kategori tidak dapat dihapus karena masih digunakan produk.'); $category->delete(); return redirect()->route('categories.index')->with('success','Kategori dihapus.'); }
}
