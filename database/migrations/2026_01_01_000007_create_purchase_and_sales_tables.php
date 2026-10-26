<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id(); $table->foreignId('supplier_id')->constrained()->restrictOnDelete(); $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('purchase_number', 40)->unique(); $table->decimal('total', 18, 2)->default(0); $table->string('status', 20)->default('completed');
            $table->timestamp('purchased_at')->index(); $table->timestamps();
        });
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('purchase_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity'); $table->decimal('unit_cost', 12, 2); $table->decimal('subtotal', 18, 2); $table->timestamps(); $table->index(['product_id','purchase_id']);
        });
        Schema::create('sales', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->restrictOnDelete(); $table->string('sale_number', 40)->unique();
            $table->decimal('total', 18, 2)->default(0); $table->string('status', 20)->default('completed'); $table->timestamp('sold_at')->index(); $table->timestamps();
        });
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('sale_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity'); $table->decimal('unit_price', 12, 2); $table->decimal('subtotal', 18, 2); $table->timestamps(); $table->index(['product_id','sale_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('sale_items'); Schema::dropIfExists('sales'); Schema::dropIfExists('purchase_items'); Schema::dropIfExists('purchases'); }
};
