<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Cart;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index($user_id)
    {
        try {
            $cart = Cart::with('items')->where('user_id', $user_id)->get();
            return $this->success('Cart Item Retrieved', [
                'cart' =>  $cart
            ], 200);
        } catch (\Throwable $th) {
            return $this->error('Error Retrieving Cart Item: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $user_id)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['sometime'],
            'product_variation_id' => ['sometime'],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
        try {
            
            $cart = Cart::with('items')->where('user_id', $user_id)->first();
            $cart->items->create([
                'product_id' => $request->product_id,
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->error('Cart Not Found!', 500);
        } catch (\Throwable $th) {
            return $this->error('Error Occur in Adding Item in cart' . $th->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cart $cart)
    {
        try {
        } catch (\Throwable $th) {
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cart $cart)
    {
        try {
        } catch (\Throwable $th) {
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $cart)
    {
        try {
        } catch (\Throwable $th) {
        }
    }
}
