<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Service;
use App\Models\ServiceReview;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    use ApiResponse;

    public function index($vendor_id)
    {
        try {
            return $this->success('All Service List retrieved successfully.', [
                'services' => Service::where('vendor_id', $vendor_id)->get()
            ], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred while retrieving the service list: " . $th->getMessage(), 500);
        }
    }

    public function store(Request $request, $vendor_id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'description' => ['required'],
            'price' => ['required'],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $user = User::find($vendor_id);
            if (!$user) {
                return $this->error("User not Found.", 404);
            }
            $service = Service::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'vendor_id' => $vendor_id
            ]);
            return $this->success('Service created Successfully.', [
                'service' => $service
            ], 200);
        } catch (\Throwable $th) {
            return $this->error("Service creation failed: " . $th->getMessage(), 500);
        }
    }

    public function show($vendor_id, $service_id)
    {
        try {
            $service = Service::with(['vendor', 'service_reviews'])->findOrFail($service_id);
            // dd($service->toArray());
            if ($service->vendor_id != $vendor_id) {
                return $this->error("Service not found in this vendor!", 404);
            }
            return $this->success('Service retrieved successfully.', [
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price' => $service->price,
                ],
                'vendor' => [
                    'id' => $service->vendor->id,
                    'name' => $service->vendor->name,
                ],
                'reviews' => $service->service_reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'quality' => $review->quality,
                        'description' => $review->description,
                        'customer_id' => $review->customer_id,
                        'created_at' => $review->created_at,
                    ];
                }),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->error("Service Not Found!" . $e->getMessage(), 404);
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return $this->error("An error occurred while retrieving the service: " . $th->getMessage(), 500);
        }
    }
    public function update(Request $request, Service $service)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required'],
            'description' => ['sometimes', 'required'],
            'price' => ['sometimes', 'required'],
            'vendor_id' => ['sometimes', 'required']
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
        try {
            $service->update($request->only(['name', 'description', 'price', 'vendor_id']));
            return $this->success('Service updated successfully.', [
                'service' => $service
            ], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred while updating the service: " . $th->getMessage(), 500);
        }
    }

    public function destroy($vendor_id, $service_id)
    {

        try {
            $service = Service::find($service_id);
            if ($service->vendor_id != $vendor_id) {
                return $this->error("Service not found in this vendor!", 404);
            }

            if ($service->user_id != auth()->user()->id) {
                return $this->error("UnAuthorized To Delete this Service", 500);
            }
            $service->delete();
            return $this->success('Service deleted successfully.', [], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred while deleting the service: " . $th->getMessage(), 500);
        }
    }
}
