<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockMovement extends Model
{
    public $timestamps = false;
    protected $fillable = ['product_id', 'user_id', 'type', 'quantity', 'note', 'created_at'];
    protected function casts(): array { return ['quantity' => 'integer', 'created_at' => 'datetime']; }
    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }
}
