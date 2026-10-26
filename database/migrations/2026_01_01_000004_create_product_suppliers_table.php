<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('product_suppliers', function (Blueprint $table) {
        $table->foreignId('product_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
        $table->foreignId('supplier_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
        $table->primary(['product_id', 'supplier_id']); $table->timestamps();
        $table->index('supplier_id');
    }); }
    public function down(): void { Schema::dropIfExists('product_suppliers'); }
};
