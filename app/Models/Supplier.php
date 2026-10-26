<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Supplier extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'phone', 'email', 'address'];
    public function products() { return $this->belongsToMany(Product::class, 'product_suppliers')->withTimestamps(); }
    public function primaryProducts() { return $this->hasMany(Product::class); }
    public function purchases() { return $this->hasMany(Purchase::class); }
}
