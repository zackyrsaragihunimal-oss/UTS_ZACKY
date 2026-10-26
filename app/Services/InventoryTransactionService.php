<?php
namespace App\Services;
use App\Models\{Product,Purchase,Sale,StockMovement};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
class InventoryTransactionService {
    private function cents(string|int|float $value): int {
        $s=trim((string)$value);
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/',$s)) throw new \InvalidArgumentException('Invalid monetary value.');
        [$whole,$fraction]=array_pad(explode('.',$s),2,'00');
        return ((int)$whole*100)+(int)str_pad($fraction,2,'0');
    }
    private function money(int $cents): string { return intdiv($cents,100).'.'.str_pad((string)($cents%100),2,'0',STR_PAD_LEFT); }
    private function number(string $prefix): string { return $prefix.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(8)); }
    public function purchase(array $data,int $userId): Purchase {
        return DB::transaction(function() use($data,$userId) {
            $ids=collect($data['items'])->pluck('product_id')->map(fn($v)=>(int)$v)->sort()->values();
            $products=Product::whereIn('id',$ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if($products->count()!==$ids->count()) throw ValidationException::withMessages(['items'=>'Salah satu produk tidak ditemukan.']);
            $purchase=Purchase::create(['supplier_id'=>$data['supplier_id'],'user_id'=>$userId,'purchase_number'=>$this->number('PO'),'total'=>'0.00','status'=>'completed','purchased_at'=>now()]);
            $total=0;
            foreach($data['items'] as $line){$product=$products->get((int)$line['product_id']);$qty=(int)$line['quantity'];$cost=$this->cents($line['unit_cost']);$sub=$cost*$qty;$total+=$sub;
                $purchase->items()->create(['product_id'=>$product->id,'quantity'=>$qty,'unit_cost'=>$this->money($cost),'subtotal'=>$this->money($sub)]);
                $product->increment('stock',$qty); $product->refresh();
                StockMovement::create(['product_id'=>$product->id,'user_id'=>$userId,'type'=>'IN','quantity'=>$qty,'note'=>'Pembelian '.$purchase->purchase_number,'created_at'=>now()]);
            }
            $purchase->update(['total'=>$this->money($total)]); return $purchase;
        },3);
    }
    public function sale(array $data,int $userId): Sale {
        return DB::transaction(function() use($data,$userId) {
            $ids=collect($data['items'])->pluck('product_id')->map(fn($v)=>(int)$v)->sort()->values();
            $products=Product::whereIn('id',$ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if($products->count()!==$ids->count()) throw ValidationException::withMessages(['items'=>'Salah satu produk tidak ditemukan.']);
            foreach($data['items'] as $line){$product=$products->get((int)$line['product_id']);if($product->stock<(int)$line['quantity'])throw ValidationException::withMessages(['items'=>'Stok produk '.$product->name.' tidak mencukupi. Tersedia '.$product->stock.' unit.']);}
            $sale=Sale::create(['user_id'=>$userId,'sale_number'=>$this->number('SO'),'total'=>'0.00','status'=>'completed','sold_at'=>now()]);$total=0;
            foreach($data['items'] as $line){$product=$products->get((int)$line['product_id']);$qty=(int)$line['quantity'];$price=$this->cents($product->selling_price);$sub=$price*$qty;$total+=$sub;
                $sale->items()->create(['product_id'=>$product->id,'quantity'=>$qty,'unit_price'=>$this->money($price),'subtotal'=>$this->money($sub)]);
                $product->decrement('stock',$qty);StockMovement::create(['product_id'=>$product->id,'user_id'=>$userId,'type'=>'OUT','quantity'=>$qty,'note'=>'Penjualan '.$sale->sale_number,'created_at'=>now()]);
            }
            $sale->update(['total'=>$this->money($total)]);return $sale;
        },3);
    }
    public function cancelSale(Sale $sale): void {
        DB::transaction(function()use($sale){$sale=Sale::whereKey($sale->id)->lockForUpdate()->firstOrFail();if($sale->status!=='completed')throw ValidationException::withMessages(['status'=>'Transaksi ini sudah dibatalkan.']);
            $items=$sale->items()->orderBy('product_id')->get();$products=Product::whereIn('id',$items->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach($items as $item){$product=$products->get($item->product_id);$product->increment('stock',$item->quantity);StockMovement::create(['product_id'=>$product->id,'user_id'=>auth()->id(),'type'=>'IN','quantity'=>$item->quantity,'note'=>'Pembatalan '.$sale->sale_number,'created_at'=>now()]);}
            $sale->update(['status'=>'cancelled']);
        },3);
    }
    public function cancelPurchase(Purchase $purchase): void {
        DB::transaction(function()use($purchase){$purchase=Purchase::whereKey($purchase->id)->lockForUpdate()->firstOrFail();if($purchase->status!=='completed')throw ValidationException::withMessages(['status'=>'Transaksi ini sudah dibatalkan.']);
            $items=$purchase->items()->orderBy('product_id')->get();$products=Product::whereIn('id',$items->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach($items as $item){$product=$products->get($item->product_id);if(!$product||$product->stock<$item->quantity)throw ValidationException::withMessages(['status'=>'Pembelian tidak dapat dibatalkan karena stok terkait sudah terpakai.']);}
            foreach($items as $item){$product=$products->get($item->product_id);$product->decrement('stock',$item->quantity);StockMovement::create(['product_id'=>$product->id,'user_id'=>auth()->id(),'type'=>'OUT','quantity'=>$item->quantity,'note'=>'Pembatalan '.$purchase->purchase_number,'created_at'=>now()]);}
            $purchase->update(['status'=>'cancelled']);
        },3);
    }
}
