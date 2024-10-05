<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use App\ApiResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use ApiResponse;


    public function index($vendor_id = null)
    {
        try {
            if ($vendor_id) {
                $products = Product::all();
            } else {
                $products = Product::where('vendor_id', $vendor_id)->get();
            }
            return $this->success("All Product Retrieved.", [
                'products' => $products
            ], 200);
        } catch (\Throwable $th) {
            $this->error("Error Occurs in Retrieving Products: " . $th->getMessage(), 500);
        }
    }

    public function store(Request $request, $vendor_id)
    {
        $validator = Validator::make($request->all(), [
            'image' => ['required', 'image'],
            'name' => ['required'],
            'base_price' => ['required', 'numeric'],
            'description' => ['required'],
            'variations' => ['required', 'array'],
            'variations.*.color' => ['required'],
            'variations.*.size' => ['required'],
            'variations.*.variation_price' => ['required', 'numeric'],
            'variations.*.stock_quantity' => ['required', 'integer'],
            'variations.*.image' => ['required', 'image'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $user = User::find($vendor_id);
            if (!$user) {
                return $this->error('Vendor not Found!', 404);
            }

            $product_image = $request->image->store('product_image', 'public');
            $product_image_path = 'storage/' . $product_image;

            $product = Product::create([
                'image' => $product_image_path,
                'name' => $request->name,
                'base_price' => $request->base_price,
                'description' => $request->description,
                'vendor_id' => $user->id
            ]);

            foreach ($request->variations as $variation) {
                $product_variation_image = $variation['image']->store('product_variation_image', 'public');
                $product_variation_image_path = 'storage/' . $product_variation_image;

                ProductVariation::create([
                    'product_id' => $product->id,
                    'size' => $variation['size'],
                    'color' => $variation['color'],
                    'variation_price' => $variation['variation_price'],
                    'stock_quantity' => $variation['stock_quantity'],
                    'image' => $product_variation_image_path
                ]);
            }

            return $this->success("Product Added Successfully!", ['product' => $product->load('variations')], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred while creating the product: " . $th->getMessage(), 500);
        }
    }

    public function show($product_id)
    {
        try {
            $product = Product::with('variations')->find($product_id);
            return $this->success("Product Retrieved ", [
                'product' => $product
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->error('Product Not Found!', 404);
        } catch (\Throwable $th) {
            return $this->error('Error Occur in Retrieving Product!', 404);
        }
    }

    public function update(Request $request, $vendor_id, $product_id)
    {
        $validator = Validator::make($request->all(), [
            'image' => ['nullable', 'image'], // Allow the image to be nullable for updates
            'name' => ['required'],
            'base_price' => ['required', 'numeric'],
            'description' => ['required'],
            'variations' => ['required', 'array'],
            'variations.*.color' => ['required'],
            'variations.*.size' => ['required'],
            'variations.*.variation_price' => ['required', 'numeric'],
            'variations.*.stock_quantity' => ['required', 'integer'],
            'variations.*.image' => ['nullable', 'image'], // Allow the image to be nullable for updates
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $user = User::find($vendor_id);
            if (!$user) {
                return $this->error('Vendor not Found!', 404);
            }

            // Find the existing product
            $product = Product::where('id', $product_id)->where('vendor_id', $user->id)->first();
            if (!$product) {
                return $this->error('Product not Found!', 404);
            }

            // Update product fields
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($product->image) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $product->image));
                }
                $product_image = $request->image->store('product_image', 'public');
                $product_image_path = 'storage/' . $product_image;
                $product->image = $product_image_path;
            }

            $product->name = $request->name;
            $product->base_price = $request->base_price;
            $product->description = $request->description;
            $product->save();

            // Update variations
            foreach ($request->variations as $variationData) {
                $variation = ProductVariation::where('product_id', $product->id)
                    ->where('color', $variationData['color'])
                    ->where('size', $variationData['size'])
                    ->first();

                if ($variation) {
                    // Update existing variation
                    if (isset($variationData['image'])) {
                        // Delete the old variation image if it exists
                        if ($variation->image) {
                            Storage::disk('public')->delete(str_replace('storage/', '', $variation->image));
                        }
                        $variation_image = $variationData['image']->store('product_variation_image', 'public');
                        $variation_image_path = 'storage/' . $variation_image;
                        $variation->image = $variation_image_path;
                    }

                    $variation->variation_price = $variationData['variation_price'];
                    $variation->stock_quantity = $variationData['stock_quantity'];
                    $variation->save();
                } else {
                    // If the variation does not exist, create it
                    $product_variation_image = $variationData['image']->store('product_variation_image', 'public');
                    $product_variation_image_path = 'storage/' . $product_variation_image;

                    ProductVariation::create([
                        'product_id' => $product->id,
                        'size' => $variationData['size'],
                        'color' => $variationData['color'],
                        'variation_price' => $variationData['variation_price'],
                        'stock_quantity' => $variationData['stock_quantity'],
                        'image' => $product_variation_image_path
                    ]);
                }
            }

            return $this->success("Product Updated Successfully!", ['product' => $product->load('variations')], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred while updating the product: " . $th->getMessage(), 500);
        }
    }


    // Remove the specified product
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->variations()->delete();
            $product->delete();
            return $this->success("Product deleted successfully.", [], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred while deleting the product.", 500);
        }
    }
}
