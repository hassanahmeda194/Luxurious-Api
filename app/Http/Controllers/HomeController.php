<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use ApiResponse;
    public function index()
    {
        try {
            $top_stylists = User::with(['services' => function ($q) {
                $q->withCount('appointments');
            }])
                ->whereHas('services', function ($q) {
                    $q->has('appointments');
                })
                ->where('role_id', 3)
                ->get()
                ->sortByDesc(function ($stylist) {
                    return $stylist->services->sum('appointments_count');
                })
                ->take(10);
            $top_products = [];

            return $this->success("Top Stylist and Top Product Retrieved", [
                'top_stylists' => $top_stylists,
                'top_products' => $top_products,
            ], 200);
        } catch (\Throwable $th) {
            return $this->error("Error on fetching Stylist and products" . $th->getMessage, 500);
        }
    }

    public function providers()
    {
        try {
            $providers =  User::with('user_infos')->where('role_id', 3)->get();
            return $this->success('All Providers Retrieved', ['providers' => $providers], 200);
        } catch (ModelNotFoundException $e) {
            return $this->error($e->getMessage(), 500);
        } catch (\Throwable $th) {
            return $this->error("Error on fetching Providers" . $th->getMessage(), 500);
        }
    }

    public function trendingStylist()
    {
        try {
            $trending_stylist = User::with(['services' => function ($q) {
                $q->withCount('appointments');
            }])
                ->whereHas('services', function ($q) {
                    $q->has('appointments');
                })
                ->where('role_id', 3)
                ->get()
                ->sortByDesc(function ($stylist) {
                    return $stylist->services->sum('appointments_count');
                });

            return $this->success("Top Stylist and Top Product Retrieved", [
                'trending_stylist' => $trending_stylist,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->error($e->getMessage(), 500);
        } catch (\Throwable $th) {
            return $this->error("Error on fetching Trending Stylist: " . $th->getMessage(), 500);
        }
    }

    public function recentProducts()
    {
        try {
            $products = Product::OrderByDesc('id')->get();
            return $this->success('All Recent Products', ['recent_products' => $products], 200);
        } catch (ModelNotFoundException $e) {
            return $this->error($e->getMessage(), 500);
        } catch (\Throwable $th) {
            return $this->error("Error on fetching recent Products" . $th->getMessage(), 500);
        }
    }

  
}
