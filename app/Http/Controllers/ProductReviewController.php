<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\ProductReview;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index($product_id)
    {
        try {
            $reviews = ProductReview::where('product_id', $product_id)->get();
            return $this->success('All Review Retrieved', [
                'reviews' => $reviews
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->error("Product Review not found!", 500);
        } catch (\Throwable $th) {
            return $this->error('Error Occur In Retrieving Reviews', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $product_id)
    {
        try {
            ProductReview::create([
                'rating' => $request->rating,
                'review' => $request->review,
                'user_id' => auth()->user()->id,
                'product_id' => $product_id,
            ]);
            return $this->success('Review Submitted Successfully', [], 200);
        } catch (\Throwable $th) {
            return $this->error('Error Occur in storing Review'  . $th->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductReview $productReview)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductReview $productReview)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($product_id, $review_id)
    {
        try {
            $review = ProductReview::find($review_id);
            $review->delete();
            return $this->success('Review Deleted Successfully', [], 200);
        } catch (ModelNotFoundException $e) {
            return $this->error('Product Reviews Not Found: '  . $e->getMessage(), 500);
        } catch (\Throwable $th) {
            return $this->error('Error Occur in Deleting Review: '  . $th->getMessage(), 500);
        }
    }
}
