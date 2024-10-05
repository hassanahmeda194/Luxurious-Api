<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    use ApiResponse;
    public function bookAppointment(Request $request, $customer_id)
    {
        $validator = Validator::make($request->all(), [
            'services' => ['required', 'array'],
            'appointment_date' => ['required', 'date_format:d-m-Y'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'number_of_people' => ['required', 'integer'],
            'user_id' => ['required', 'exists:users,id'],
            'total_amount' => ['required']
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        DB::beginTransaction();
        try {
            $appointment = Appointment::create([
                'appointment_id' => $this->generateAppointmentID(),
                'customer_id' => $customer_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'number_of_people' => $request->number_of_people,
                'total_amount' => $request->total_amount
            ]);
            $appointment->services()->attach($request->services);
            DB::commit();
            return $this->success('Appointment booked successfully.', [
                'appointment' => $appointment
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error(
                message: 'Appointment booking failed: ' . $th->getMessage(),
                statusCode: 500
            );
        }
    }

    private function generateAppointmentID()
    {
        $last_appointment = Appointment::orderByDesc('id')->first();
        $next_id = $last_appointment ? $last_appointment->id + 1 : 1;
        return str_pad($next_id, 4, '0', STR_PAD_LEFT);
    }

    public function getAppointmentsByStatus(Request $request, $customer_id)
    {
        try {
            $query = Appointment::where('customer_id', $customer_id);
            if ($request->status == 'pending') {
                $query->where('status', 'pending');
            }
            if ($request->status == 'confirmed') {
                $query->where('status', 'confirmed');
            }
            if ($request->status == 'canceled') {
                $query->where('status', 'canceled');
            }
            $appointments = $query->get();
            return $this->success('Appointments retrieved successfully.', [
                'appointments' => $appointments,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->error('No appointments found for this user.', 404);
        } catch (\Throwable $th) {
            return $this->error('An error occurred while retrieving appointments: ' . $th->getMessage(), 500);
        }
    }

    public function vendorAppointment($vendor_id, Request $request)
    {
        try {
            // Fetch appointments with their related services and customers
            $appointments = Appointment::with(['services', 'customer'])
                ->whereHas('services', function ($query) use ($vendor_id) {
                    $query->where('vendor_id', $vendor_id);
                });

            if ($request->status == 'confirmed') {
                $appointments->where('status', 'confirmed');
            }
            if ($request->status == 'canceled') {
                $appointments->where('status', 'canceled');
            }
            if ($request->status == 'pending') {
                $appointments->where('status', 'pending');
            }

            $appointments = $appointments->get();

            // Prepare the response structure
            return $this->success('All vendor appointments retrieved successfully.', [
                'appointments' => $appointments->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'appointment_id' => $appointment->appointment_id,
                        'appointment_date' => $appointment->appointment_date,
                        'appointment_time' => $appointment->appointment_time,
                        'number_of_people' => $appointment->number_of_people,
                        'status' => $appointment->status,
                        'total_amount' => $appointment->total_amount,
                        'services' => $appointment->services->map(function ($service) {
                            return [
                                'id' => $service->id,
                                'name' => $service->name,
                            ];
                        }),
                        'customer' => [
                            'id' => $appointment->customer->id,
                            'name' => $appointment->customer->name,
                            'email' => $appointment->customer->email,
                        ],
                    ];
                })
            ], 200);
        } catch (\Throwable $th) {
            return $this->error('Error in retrieving vendor appointments: ' . $th->getMessage(), 500);
        }
    }

    public function updateAppointmentStatus(Request $request, $appointment_id)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'in:pending,canceled,confirmed'],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
        try {
            $appointment =  Appointment::find($appointment_id);
            $appointment->update([
                'status' => $request->status
            ]);
            return $this->success("Appointment Status Updated Successfully",[], 200);
        } catch (ModelNotFoundException $e) {
            return $this->error('Appointment Not Found!', 500);
        } catch (\Throwable $th) {
            return $this->error('Error Occur in updating Appointment', 500);
        }
    }
}
