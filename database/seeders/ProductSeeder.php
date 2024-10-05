<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Color;
use App\Models\Size;
use App\Models\User;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run(): void
    {

        $colors = Color::all()->pluck('id')->toArray();
        $sizes = Size::all()->pluck('id')->toArray();

        for ($i = 0; $i < 10; $i++) {
            $product = Product::create([
                'image' => '/storage/products/product.jpg',
                'name' => fake()->word(),
                'price' => fake()->randomFloat(2, 10, 500),
                'description' => fake()->sentence(),
                'user_id' => User::where('role_id', 2)->inRandomOrder()->first()->id
            ]);
            for ($j = 0; $j < 3; $j++) {
                ProductVariation::create([
                    'product_id' => $product->id,
                    'color_id' => fake()->randomElement($colors),
                    'size_id' => fake()->randomElement($sizes),
                    'variation_price' => fake()->randomFloat(2, 10, 500),
                    'variation_quantity' => fake()->numberBetween(1, 100),
                ]);
            }
        }
    }
}
