<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = ['name', 'username', 'email', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed']; }
    public function stockMovements() { return $this->hasMany(StockMovement::class); }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function purchases() { return $this->hasMany(Purchase::class); }
    public function sales() { return $this->hasMany(Sale::class); }
}
