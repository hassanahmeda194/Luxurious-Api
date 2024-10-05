<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\ServiceReview;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceReviewController extends Controller
{
    use ApiResponse;

    public function index($service_id)
    {
        try {
            $reviews =  ServiceReview::where('service_id', $service_id)->get();
            return $this->success("All Reviews Retrieved", [
                'data' => $reviews
            ], 200);
        } catch (ModelNotFoundException $e) {
            $this->error(message: "Service not found", statusCode: 500);
        } catch (\Throwable $th) {
            $this->error(message: "Error Occur in Retrieving a reviews", statusCode: 500);
        }
    }


    public function store(Request $request, $service_id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => ['required'],
            'quality' => ['required'],
            'description' => ['required'],
            'customer_id' => ['required'],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
        try {
            ServiceReview::create([
                'rating' => $request->rating,
                'quality' => $request->quality,
                'description' => $request->description,
                'service_id' => $service_id,
                'customer_id' => auth()->user()->id
            ]);
            return $this->success("Service Review submitted Successfully", [], 200);
        } catch (ModelNotFoundException $e) {
            $this->error(message: "Service Review not found", statusCode: 500);
        } catch (\Throwable $th) {
            $this->error(message: "Error Occur in storing a reviews", statusCode: 500);
        }
    }

    public function destroy($service_id, $review_id)
    {
        try {
            $review = ServiceReview::find($review_id);
            $review->delete();
            return $this->success("Review Deleted submitted Successfully", [], 200);
        } catch (ModelNotFoundException $e) {
            $this->error(message: "Review not found", statusCode: 500);
        } catch (\Throwable $th) {
            $this->error(message: "Error Occur in Deleted a reviews", statusCode: 500);
        }
    }
}
