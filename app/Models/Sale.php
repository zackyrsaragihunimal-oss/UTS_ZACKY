<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Sale extends Model {
    protected $fillable=['user_id','sale_number','total','status','sold_at'];
    protected function casts(): array {return ['total'=>'decimal:2','sold_at'=>'datetime'];}
    public function user(){return $this->belongsTo(User::class);} public function items(){return $this->hasMany(SaleItem::class);}
    public function scopeCompleted($q){return $q->where('status','completed');}
}
