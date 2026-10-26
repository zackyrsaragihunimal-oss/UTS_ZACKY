<?php
namespace Database\Factories;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
class ProductFactory extends Factory
{
    public function definition(): array { return [
        'sku' => fake()->unique()->bothify('SKU-####'),
        'name' => fake()->words(3, true),
        'category_id' => Category::factory(),
        'supplier_id' => Supplier::factory(),
        'price' => fake()->randomFloat(2, 10000, 500000),
        'purchase_price' => fake()->randomFloat(2, 5000, 300000), 'selling_price' => fake()->randomFloat(2, 10000, 500000),
        'stock' => fake()->numberBetween(0, 100), 'minimum_stock' => 5,
    ]; }
}
