<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) { $table->string('email')->nullable()->unique()->after('username'); });
        DB::table('users')->whereNull('email')->orderBy('id')->get(['id','username'])->each(function ($user) { DB::table('users')->where('id',$user->id)->update(['email' => 'legacy-user-'.$user->id.'@example.test']); });
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('purchase_price', 12, 2)->default(0)->after('price');
            $table->decimal('selling_price', 12, 2)->default(0)->after('purchase_price');
            $table->unsignedInteger('minimum_stock')->default(5)->after('stock');
        });
        DB::table('products')->update(['purchase_price' => DB::raw('price'), 'selling_price' => DB::raw('price')]);
    }
    public function down(): void {
        Schema::table('products', function (Blueprint $table) { $table->dropColumn(['purchase_price','selling_price','minimum_stock']); });
        Schema::table('users', function (Blueprint $table) { $table->dropUnique(['email']); $table->dropColumn('email'); });
    }
};
