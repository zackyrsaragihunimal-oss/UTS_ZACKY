<?php
namespace App\Http\Controllers;
use App\Models\{Category,Product,Purchase,Sale,Supplier};use Illuminate\Http\Request;
class DashboardController extends Controller {
 public function index(Request $request){$user=$request->user();$sales=Sale::completed()->when(!$user->isAdmin(),fn($q)=>$q->where('user_id',$user->id));$purchases=Purchase::completed()->when(!$user->isAdmin(),fn($q)=>$q->where('user_id',$user->id));
  $stats=['Produk'=>Product::count(),'Transaksi'=>((clone $sales)->count()+(clone $purchases)->count()),'Penjualan hari ini'=>(clone $sales)->whereDate('sold_at',today())->sum('total'),'Penjualan bulan ini'=>(clone $sales)->whereMonth('sold_at',now()->month)->whereYear('sold_at',now()->year)->sum('total')];
  $lowStock=Product::with('category')->whereColumn('stock','<=','minimum_stock')->orderBy('stock')->limit(10)->get();$recentSales=(clone $sales)->with('user')->latest('sold_at')->limit(8)->get();return view('dashboard',compact('stats','lowStock','recentSales'));}
}
