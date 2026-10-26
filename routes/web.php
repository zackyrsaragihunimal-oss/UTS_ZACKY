<?php
use App\Http\Controllers\{AuthController,CategoryController,DashboardController,ProductController,PurchaseController,ReportController,SaleController,StockController,SupplierController,UserController};
use Illuminate\Support\Facades\Route;
Route::get('/',fn()=>auth()->check()?redirect()->route('dashboard'):redirect()->route('login'));
Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'showLogin'])->name('login');Route::post('/login',[AuthController::class,'login'])->name('login.store');});
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function(){
 Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
 Route::get('/products',[ProductController::class,'index'])->name('products.index');
 Route::middleware('admin')->group(function(){Route::get('/products/create',[ProductController::class,'create'])->name('products.create');Route::post('/products',[ProductController::class,'store'])->name('products.store');Route::get('/products/{product}/edit',[ProductController::class,'edit'])->name('products.edit');Route::put('/products/{product}',[ProductController::class,'update'])->name('products.update');Route::delete('/products/{product}',[ProductController::class,'destroy'])->name('products.destroy');
  Route::resource('categories',CategoryController::class);Route::resource('suppliers',SupplierController::class);Route::get('/users',[UserController::class,'index'])->name('users.index');Route::post('/users',[UserController::class,'store'])->name('users.store');
  Route::post('/purchases/{purchase}/cancel',[PurchaseController::class,'cancel'])->name('purchases.cancel');Route::post('/sales/{sale}/cancel',[SaleController::class,'cancel'])->name('sales.cancel');Route::get('/reports',[ReportController::class,'index'])->name('reports.index');Route::get('/stock',[StockController::class,'index'])->name('stock.index');Route::post('/stock',[StockController::class,'store'])->name('stock.store');
 });
 Route::get('/products/{product}',[ProductController::class,'show'])->name('products.show');
 Route::get('/purchases',[PurchaseController::class,'index'])->name('purchases.index');Route::get('/purchases/create',[PurchaseController::class,'create'])->name('purchases.create');Route::post('/purchases',[PurchaseController::class,'store'])->name('purchases.store');Route::get('/purchases/{purchase}',[PurchaseController::class,'show'])->name('purchases.show');
 Route::get('/sales',[SaleController::class,'index'])->name('sales.index');Route::get('/sales/create',[SaleController::class,'create'])->name('sales.create');Route::post('/sales',[SaleController::class,'store'])->name('sales.store');Route::get('/sales/{sale}',[SaleController::class,'show'])->name('sales.show');
});
